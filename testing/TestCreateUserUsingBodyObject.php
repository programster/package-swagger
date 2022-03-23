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
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\MappedResponse;
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
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;

use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Security\HttpBasicAuth;


require_once(__DIR__ . '/../vendor/autoload.php');


function main()
{
    $jwtSecurityScheme = new NamedSecurityScheme("jwt", new JwtBearer());
    $jwtBearerSecurityRequirement = $jwtSecurityScheme->createSecurityRequirement([]);

    $successResponseSchema = new SchemaObject(
        description: "A success response object",
        properties: new SchemaMap([
            "message" => new SchemaString()
        ])
    );

    $exampleSuccessResponse = new ValueExample(
        ['message' => "Successfully did x."]
    );

    $genericSuccessResponse = new Response(
        description: "A generic success response.",
        contentMap: new ContentMap(
            key: MimeType::JSON,
            mediaType: new MediaType(
                schema: $successResponseSchema,
                example: $exampleSuccessResponse
            )
        )
    );

    $userProperties = new SchemaMap([
        'id' => new SchemaString(format: Format::UUID),
        'first_name' => new SchemaString(minLength: 3, maxLength: 255),
        'last_name' => new SchemaString(minLength: 3, maxLength: 255),
    ]);

    $userObject = new SchemaObject(
        description: "A user",
        properties: $userProperties,
        requiredProperties: ['first_name', 'last_name'],
    );

    $userMediaType = new MediaType(schema: $userObject);

    $userRequestBody = new RequestBody(
        description: "Object representing the user to create.",
        content: new ContentMap(MimeType::JSON, $userMediaType),
        required: true,
    );

    // Possible responses for sending a requst to create a user.
    $createUserResponses = new ResponseCollection(
        new MappedResponse(code: ResponseCode::OK, response: $genericSuccessResponse)
    );

    // create user using posted JSON body.
    $createUserOperation = new Operation(
        method: Method::POST,
        responses: $createUserResponses,
        summary: "Add a user to the system",
        description:"Add a user to the system",
        requestBody: $userRequestBody,
        security: new SecurityRequirementCollection($jwtBearerSecurityRequirement)
    );

    $basicAuth = new HttpBasicAuth();

    $info = new Info(
        title: "My RESTful API",
        version: "1.0",
        description: "A RESTful API to integrate with something.",
        contact: new Contact("Programster", null, "myEmail@gmail.com"),
        license: new License("MIT", "https://opensource.org/licenses/MIT")
    );

    $usersPath = new Path(relativePath: "/users", operations: $createUserOperation);
    $paths = new PathCollection($usersPath);
    $securitySchemes = new SecuritySchemeMap($jwtSecurityScheme);
    $components = new Components(securitySchemes: $securitySchemes);

    $document = new OpenApi(
        info: $info,
        paths: $paths,
        security: new SecurityRequirementCollection($jwtBearerSecurityRequirement),
        components: $components
    );

    //file_put_contents(__DIR__ . '/swagger.json', $document);
    print $document . PHP_EOL;
    //print "Swagger documentation updated. Don't forget to commit." . PHP_EOL;
}


main();

