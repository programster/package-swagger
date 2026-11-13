<?php

/*
 * Smoke tests for OpenApi, the document itself, including building a whole document and comparing it against
 * testing/assets/users-api.json. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Components;
use Programster\Swagger\Contact;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\InvalidDocumentException;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\RequestBody;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\ApiKeyLocation;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\OAuth2;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\Tag;
use Programster\Swagger\TagCollection;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createOperation = function (
    Method $method,
    ?string $operationId = null,
    ?SecurityRequirementCollection $security = null
) : Operation {
    $responses = new ResponseCollection(new MappedResponse(ResponseCode::OK, new Response("OK.")));
    return new Operation($method, $responses, operationId: $operationId, security: $security);
};

$createDocument = function (PathItem $item, Components $components = new Components()) : OpenApi {
    $paths = new PathCollection(new Path("/users", $item));
    return new OpenApi(new Info("My API", "1.0"), $paths, components: $components);
};

$testOpenApiOutput = function () use ($createOperation, $createDocument) : void {
    $document = $createDocument(new PathItem(new OperationCollection($createOperation(Method::GET))));

    $expected = <<<'JSON'
        {
            "openapi": "3.0.3",
            "info": {
                "title": "My API",
                "version": "1.0"
            },
            "paths": {
                "/users": {
                    "get": {
                        "responses": {
                            "200": {
                                "description": "OK."
                            }
                        }
                    }
                }
            }
        }
        JSON;

    assert($document->toJson() === $expected, "toJson() gave: " . $document->toJson());
    assert((string)$document === $expected);

    // Response codes are quoted in YAML, as OpenAPI requires, rather than being output as numbers.
    $expectedYaml = <<<'YAML'
        ---
        openapi: 3.0.3
        info:
          title: My API
          version: "1.0"
        paths:
          /users:
            get:
              responses:
                "200":
                  description: OK.
        ...

        YAML;

    assert($document->toYaml() === $expectedYaml, "toYaml() gave: " . $document->toYaml());
    assert(yaml_parse($document->toYaml()) === json_decode($expected, true));

    // Empty objects stay objects in YAML, rather than becoming lists.
    $flows = new OauthFlows(clientCredentials: new ClientCredentialsFlow("https://example.com/token", new ScopeMap()));
    $oauth = new NamedSecurityScheme("oauth", new OAuth2($flows));
    $withEmptyScopes = $createDocument(new PathItem(), new Components(securitySchemes: new SecuritySchemeMap($oauth)));
    assert(str_contains($withEmptyScopes->toYaml(), "scopes: {}"), $withEmptyScopes->toYaml());
    assert(str_contains($withEmptyScopes->toYaml(), "/users: {}"), $withEmptyScopes->toYaml());
};

$smoke->run("OpenApi toJson(), __toString() and toYaml()", $testOpenApiOutput);

$testOpenApiChecks = function () use ($createOperation, $createDocument) : void {
    $apiKey = new NamedSecurityScheme("apiKey", new ApiKey("api_key", ApiKeyLocation::HEADER));
    $requireApiKey = new SecurityRequirementCollection($apiKey->createSecurityRequirement());
    $components = new Components(securitySchemes: new SecuritySchemeMap($apiKey));
    $operations = new OperationCollection($createOperation(Method::GET, null, $requireApiKey));
    $createDocument(new PathItem($operations), $components);

    $useMissingSchemeInOperation = function () use ($createOperation, $createDocument, $requireApiKey) : void {
        $createDocument(new PathItem(new OperationCollection($createOperation(Method::GET, null, $requireApiKey))));
    };

    SmokeTest::expectException(
        InvalidDocumentException::class,
        $useMissingSchemeInOperation,
        'The security of GET /users uses the "apiKey" security scheme, but the components don\'t have one'
    );

    $useMissingSchemeInDocument = function () use ($requireApiKey) : void {
        new OpenApi(new Info("My API", "1.0"), new PathCollection(), security: $requireApiKey);
    };

    SmokeTest::expectException(
        InvalidDocumentException::class,
        $useMissingSchemeInDocument,
        "The document's security uses the \"apiKey\" security scheme"
    );

    $useOperationIdTwice = function () use ($createOperation) : void {
        $users = new Path("/users", new PathItem(new OperationCollection($createOperation(Method::GET, "list"))));
        $groups = new Path("/groups", new PathItem(new OperationCollection($createOperation(Method::GET, "list"))));
        new OpenApi(new Info("My API", "1.0"), new PathCollection($users, $groups));
    };

    SmokeTest::expectException(
        InvalidDocumentException::class,
        $useOperationIdTwice,
        'GET /groups and GET /users both have the operationId "list"'
    );
};

$smoke->run("OpenApi checks", $testOpenApiChecks);

$testWholeDocument = function () : void {
    $user = new NamedSchema("User", new SchemaObject(
        description: "A user.",
        properties: new PropertyMap(
            new Property("id", new SchemaString(format: StringFormat::UUID), true),
            new Property("name", new SchemaString(minLength: 3, maxLength: 255), true),
            new Property("age", new SchemaInt(minimum: 0)),
        ),
    ));

    $userExample = new ValueExample(['id' => "3f2b8c1e-9a4d-4e7b-b6f0-2d5c8e1a7f93", 'name' => "Bob"]);
    $rateLimitHeader = Header::createFromSchema(new SchemaInt(), description: "Requests left.");
    $rateLimit = new MappedHeader("X-Rate-Limit", $rateLimitHeader);

    $userResponse = new Response(
        "The user.",
        new ContentMap(new MediaType(MimeType::JSON, $user->createReference(), $userExample)),
        new HeaderCollection($rateLimit)
    );

    $scopes = new ScopeMap(new Scope("users:read", "Read users."), new Scope("users:write", "Create users."));
    $tokenFlow = new ClientCredentialsFlow("https://auth.example.com/token", $scopes);
    $oauth = new NamedSecurityScheme("oauth", new OAuth2(new OauthFlows(clientCredentials: $tokenFlow)));
    $apiKey = new NamedSecurityScheme("apiKey", new ApiKey("X-API-Key", ApiKeyLocation::HEADER));
    $readSecurity = [$oauth->createSecurityRequirement("users:read"), $apiKey->createSecurityRequirement()];

    $getUser = new Operation(
        method: Method::GET,
        responses: new ResponseCollection(new MappedResponse(ResponseCode::OK, $userResponse)),
        summary: "Get a user.",
        tags: ["users"],
        operationId: "getUser",
        security: new SecurityRequirementCollection(...$readSecurity),
    );

    $userBody = new RequestBody(
        new ContentMap(new MediaType(MimeType::JSON, $user->createReference())),
        "The user to create.",
        true
    );

    $createUser = new Operation(
        method: Method::POST,
        responses: new ResponseCollection(new MappedResponse(ResponseCode::CREATED, $userResponse)),
        summary: "Create a user.",
        requestBody: $userBody,
        tags: ["users"],
        operationId: "createUser",
        security: new SecurityRequirementCollection($oauth->createSecurityRequirement("users:write")),
    );

    $idSchema = new SchemaString(format: StringFormat::UUID);
    $idParameter = Parameter::createFromSchema("id", ParameterLocation::PATH, $idSchema);
    $userItem = new PathItem(new OperationCollection($getUser), parameters: new ParameterCollection($idParameter));

    $document = new OpenApi(
        info: new Info(
            title: "Users API",
            version: "1.0",
            description: "An API for managing users.",
            contact: new Contact("Support", null, "support@example.com"),
            license: new License("MIT", "https://opensource.org/licenses/MIT"),
        ),
        paths: new PathCollection(
            new Path("/users/{id}", $userItem),
            new Path("/users", new PathItem(new OperationCollection($createUser))),
        ),
        servers: new ServerCollection(new Server("https://api.example.com/v1", "Production")),
        components: new Components(
            schemas: new SchemaMap($user),
            securitySchemes: new SecuritySchemeMap($oauth, $apiKey),
        ),
        tags: new TagCollection(new Tag("users", "Managing users.")),
        externalDocs: new ExternalDocumentation("https://docs.example.com"),
    );

    SmokeTest::assertJson(file_get_contents(__DIR__ . '/../assets/users-api.json'), $document);
};

$smoke->run("OpenApi whole document", $testWholeDocument);
