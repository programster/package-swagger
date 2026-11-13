<?php

/*
 * Smoke tests for Components. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Callback;
use Programster\Swagger\CallbackMap;
use Programster\Swagger\Components;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedCallback;
use Programster\Swagger\NamedHeader;
use Programster\Swagger\NamedLink;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\NamedRequestBody;
use Programster\Swagger\NamedResponse;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\PathItem;
use Programster\Swagger\RequestBody;
use Programster\Swagger\RequestBodyMap;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseMap;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testComponents = function () : void {
    $empty = new Components();
    assert($empty->isEmpty());
    SmokeTest::assertJson('{}', $empty);

    $search = Parameter::createFromSchema("q", ParameterLocation::QUERY, new SchemaString());
    $textBody = new RequestBody(new ContentMap(new MediaType(MimeType::PLAIN_TEXT)));

    $components = new Components(
        schemas: new SchemaMap(new NamedSchema("Name", new SchemaString())),
        responses: new ResponseMap(new NamedResponse("NotFound", new Response("Not found."))),
        parameters: new ParameterMap(new NamedParameter("search", $search)),
        examples: new ExampleMap(new NamedExample("one", new ValueExample(1))),
        requestBodies: new RequestBodyMap(new NamedRequestBody("Text", $textBody)),
        headers: new HeaderMap(new NamedHeader("RateLimit", Header::createFromSchema(new SchemaString()))),
        securitySchemes: new SecuritySchemeMap(new NamedSecurityScheme("jwt", new JwtBearer())),
        links: new LinkMap(new NamedLink("Next", Link::createFromOperationId("getNext"))),
        callbacks: new CallbackMap(new NamedCallback("Hook", new Callback('{$request.body#/url}', new PathItem()))),
    );

    $expected = <<<'JSON'
        {
            "schemas": {"Name": {"type": "string"}},
            "responses": {"NotFound": {"description": "Not found."}},
            "parameters": {"search": {"name": "q", "in": "query", "schema": {"type": "string"}}},
            "examples": {"one": {"value": 1}},
            "requestBodies": {"Text": {"content": {"text/plain": {}}}},
            "headers": {"RateLimit": {"schema": {"type": "string"}}},
            "securitySchemes": {"jwt": {"type": "http", "scheme": "bearer", "bearerFormat": "JWT"}},
            "links": {"Next": {"operationId": "getNext"}},
            "callbacks": {"Hook": {"{$request.body#/url}": {}}}
        }
        JSON;

    assert(!$components->isEmpty());
    SmokeTest::assertJson($expected, $components);
    assert($components->getSecuritySchemes()->getKeys() === ["jwt"]);

    // Examples can go elsewhere, where any name is allowed, so the components check their names themselves.
    $createWithBadExampleName = function () : void {
        new Components(examples: new ExampleMap(new NamedExample("an example", new ValueExample(1))));
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithBadExampleName,
        "can only contain letters, numbers"
    );
};

$smoke->run("Components", $testComponents);
