<?php

/*
 * Smoke tests for OpenApi, the document itself, including building a whole document and comparing it against
 * testing/assets/users-api.json. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Components;
use Programster\Swagger\Contact;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Format;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\Reference;
use Programster\Swagger\RequestBody;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\Tag;
use Programster\Swagger\TagCollection;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createMinimalDocument = function () : OpenApi {
    $responses = new ResponseCollection(new MappedResponse(ResponseCode::OK, new Reference("#/responses/Ok")));
    $path = new Path("/users/{id}", new OperationCollection(new Operation(Method::GET, $responses)));
    return new OpenApi(new Info("My API", "1.0"), new PathCollection($path));
};

$testOpenApiOutput = function () use ($createMinimalDocument) : void {
    $document = $createMinimalDocument();

    $expected = <<<'JSON'
        {
            "openapi": "3.0.3",
            "info": {
                "title": "My API",
                "version": "1.0"
            },
            "paths": {
                "/users/{id}": {
                    "get": {
                        "responses": {
                            "200": {
                                "$ref": "#/responses/Ok"
                            }
                        }
                    }
                }
            }
        }
        JSON;

    // The JSON is pretty printed, and slashes are no longer escaped (e.g. "\/users"), as the flag for that used to
    // be passed as json_encode()'s depth.
    assert($document->toJson() === $expected, "toJson() gave: " . $document->toJson());
    assert((string)$document === $expected);
    SmokeTest::assertJson($expected, $document->toArray());

    $yaml = $document->toYaml();
    assert(str_contains($yaml, "openapi: 3.0.3"), "toYaml() gave: {$yaml}");
    assert(yaml_parse($yaml) === json_decode($expected, true), "toYaml() gave: {$yaml}");
};

$smoke->run("OpenApi toJson(), __toString() and toYaml()", $testOpenApiOutput);

$testOpenApiDuplicatePaths = function () : void {
    $createWithDuplicatePaths = function () : void {
        new OpenApi(new Info("My API", "1.0"), new PathCollection(new Path("/users", null), new Path("/users", null)));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicatePaths, "the same relative path: /users");
};

$smoke->run("OpenApi duplicate paths", $testOpenApiDuplicatePaths);

$testWholeDocument = function () : void {
    $userSchema = new SchemaObject(
        description: "A user.",
        properties: new SchemaMap(
            new NamedSchema("id", new SchemaString(format: Format::UUID)),
            new NamedSchema("name", new SchemaString(minLength: 3, maxLength: 255)),
        ),
        requiredProperties: ["name"],
    );

    $userResponse = new Response(
        "The user.",
        new ContentMap(
            MimeType::JSON,
            new MediaType(Reference::createReferenceObject("User"), new ValueExample(['id' => "abc", 'name' => "Bob"]))
        ),
        new HeaderMap(Header::createFromSchema("X-Rate-Limit", new SchemaString(), description: "Requests left."))
    );

    $getUser = new Operation(
        method: Method::GET,
        responses: new ResponseCollection(new MappedResponse(ResponseCode::OK, $userResponse)),
        summary: "Get a user.",
        parameters: new ParameterCollection(
            Parameter::createFromSchema("id", ParameterLocation::PATH, true, new SchemaString(format: Format::UUID))
        ),
        tags: ["users"],
        operationId: "getUser",
    );

    $createUser = new Operation(
        method: Method::POST,
        responses: new ResponseCollection(new MappedResponse(ResponseCode::CREATED, $userResponse)),
        summary: "Create a user.",
        requestBody: new RequestBody(
            new ContentMap(MimeType::JSON, new MediaType(Reference::createReferenceObject("User"))),
            "The user to create.",
            true
        ),
        tags: ["users"],
        operationId: "createUser",
    );

    $apiKey = new NamedSecurityScheme("apiKey", new ApiKey("api_key", ParameterLocation::HEADER));

    $document = new OpenApi(
        info: new Info(
            title: "Users API",
            version: "1.0",
            description: "An API for managing users.",
            contact: new Contact("Support", null, "support@example.com"),
            license: new License("MIT", "https://opensource.org/licenses/MIT"),
        ),
        paths: new PathCollection(
            new Path("/users/{id}", new OperationCollection($getUser)),
            new Path("/users", new OperationCollection($createUser)),
        ),
        servers: new ServerCollection(new Server("https://api.example.com/v1", "Production", null)),
        components: new Components(
            schemas: new SchemaMap(new NamedSchema("User", $userSchema)),
            securitySchemes: new SecuritySchemeMap($apiKey),
        ),
        security: new SecurityRequirementCollection($apiKey->createSecurityRequirement([])),
        tags: new TagCollection(new Tag("users", "Managing users.")),
        externalDocs: new ExternalDocumentation("https://docs.example.com"),
    );

    SmokeTest::assertJson(file_get_contents(__DIR__ . '/../assets/users-api.json'), $document);
};

$smoke->run("OpenApi whole document", $testWholeDocument);
