<?php

/*
 * A script to generate the swagger JSON documentation.
 */

namespace Programster\Swagger\Testing;

use Programster\Swagger\Components;
use Programster\Swagger\Contact;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Format;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\NamedSchema;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\RequestBody;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\SchemaMap;
use Programster\Swagger\SchemaObject;
use Programster\Swagger\OpenApi;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\Path;
use Programster\Swagger\Operation;
use Programster\Swagger\PathCollection;
use Programster\Swagger\Response;
use Programster\Swagger\SchemaString;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;


require_once(__DIR__ . '/../vendor/autoload.php');


function main()
{
    $paths = new PathCollection(
        getUsersPath(),
    );

    $basicAuthSecurityScheme = new NamedSecurityScheme("apiKey", new ApiKey("api_key", ParameterLocation::HEADER));
    $securityRequirement = $basicAuthSecurityScheme->createSecurityRequirement([]);
    $securitySchemes = new SecuritySchemeMap($basicAuthSecurityScheme);

    $authHeader = Header::createFromSchema(
        name: "Authorization",
        schema: new SchemaString(),
        required: true,
        description: "The HTTP auth header.",
    );

    $authHeaders = new HeaderMap($authHeader);

    $components = new Components(
        headers: $authHeaders,
        securitySchemes: $securitySchemes,
    );

    $info = new Info(
        title: "My RESTful API",
        version: "1.0",
        description: "A RESTful API to integrate with something.",
        contact: new Contact("Programster", null, "myEmail@gmail.com"),
        license: new License("MIT", "https://opensource.org/licenses/MIT")
    );

    $document = new OpenApi(
        info: $info,
        paths: $paths,
        components: $components,
        security: new SecurityRequirementCollection($securityRequirement),
    );

    print $document . PHP_EOL;
}


function getCreateUserOperation() : Operation
{
    $userProperties = new SchemaMap(
        new NamedSchema('id', new SchemaString(format: Format::UUID)),
        new NamedSchema('first_name', new SchemaString(minLength: 3, maxLength: 255)),
        new NamedSchema('last_name', new SchemaString(minLength: 3, maxLength: 255)),
    );

    $userObject = new SchemaObject(
        description: "A user",
        properties: $userProperties,
        requiredProperties: ['first_name', 'last_name'],
    );

    $userMediaType = new MediaType(schema: $userObject);

    $userRequestBody = new RequestBody(
        content: new ContentMap(MimeType::JSON, $userMediaType),
        description: "Object representing the user to create.",
        required: true,
    );

    // Possible responses for sending a requst to create a user.
    $responses = new ResponseCollection(
        getGenericSuccessResponse(),
    );

    // create user using posted JSON body.
    return new Operation(
        method: Method::POST,
        responses: $responses,
        parameters: new ParameterCollection(Parameter::createFromSchema(name:"api_key", in: ParameterLocation::HEADER, required: true, schema: new SchemaString())),
        summary: "Add a user to the system",
        description:"Add a user to the system",
        requestBody: $userRequestBody,
        tags: ["users"],
    );
}


function getGenericSuccessResponse() : MappedResponse
{
    $exampleSuccessResponse = new ValueExample(
        ['message' => "Successfully did x."]
    );

    $successResponseSchema = new SchemaObject(
        description: "A success response object",
        properties: new SchemaMap(
            new NamedSchema("message", new SchemaString())
        )
    );

    return new MappedResponse(
        code: ResponseCode::OK,
        response: new Response(
            description: "A generic success response.",
            contentMap: new ContentMap(
                key: MimeType::JSON,
                mediaType: new MediaType(
                    schema: $successResponseSchema,
                    example: $exampleSuccessResponse
                )
            )
        )
    );
}


/**
 * @return Path
 * @throws \Exception
 */
function getUsersPath() : Path
{
    return new Path(
        relativePath: "/users",
        operations: new OperationCollection(
            getFetchUsersOperation(),
            getCreateUserOperation(),
        ),
    );
}


function getFetchUsersOperation() : Operation
{
    $userProperties = new SchemaMap(
        new NamedSchema('id', new SchemaString(format: Format::UUID)),
        new NamedSchema('first_name', new SchemaString(minLength: 3, maxLength: 255)),
        new NamedSchema('last_name', new SchemaString(minLength: 3, maxLength: 255)),
    );

    $userObject = new SchemaObject(
        description: "A user",
        properties: $userProperties,
        requiredProperties: ['first_name', 'last_name'],
    );

    // Possible responses for sending a requst to create a user.
    $responses = new ResponseCollection(
        getGenericSuccessResponse(),
    );

    // create user using posted JSON body.
    return new Operation(
        method: Method::GET,
        responses: $responses,
        summary: "Fetch the users in the system.",
        description: "Fetch the users in the system.",
        tags: ["users"],
    );
}

main();

