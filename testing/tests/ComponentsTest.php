<?php

/*
 * Smoke tests for Components. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Callbacks;
use Programster\Swagger\Components;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\LinkMap;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\NamedCallback;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\Reference;
use Programster\Swagger\RequestBodyMap;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testComponents = function () : void {
    assert((new Components())->jsonSerialize() === []);

    // Passing schemas or callbacks used to crash, as their types named classes that don't exist.
    $components = new Components(
        schemas: new SchemaMap(new NamedSchema("Name", new SchemaString())),
        responses: new ResponseCollection(new MappedResponse(ResponseCode::OK, new Reference("#/a"))),
        parameters: new ParameterMap(new NamedParameter("id", new Reference("#/b"))),
        examples: new ExampleMap(new NamedExample("one", new ValueExample(1))),
        requestBodies: new RequestBodyMap("user", new Reference("#/c")),
        headers: new HeaderMap(Header::createFromSchema("X-A", new SchemaString())),
        securitySchemes: new SecuritySchemeMap(new NamedSecurityScheme("jwt", new JwtBearer())),
        links: new LinkMap("group", new Reference("#/d")),
        callbacks: new Callbacks(new NamedCallback("onCreate", new Reference("#/e"))),
    );

    $expected = <<<'JSON'
        {
            "schemas": {"Name": {"type": "string"}},
            "responses": {"200": {"$ref": "#/a"}},
            "parameters": {"id": {"$ref": "#/b"}},
            "examples": {"one": {"value": 1}},
            "requestBodies": {"user": {"$ref": "#/c"}},
            "headers": {"X-A": {"schema": {"type": "string"}}},
            "securitySchemes": {"jwt": {"type": "http", "scheme": "bearer", "bearerFormat": "JWT"}},
            "links": {"group": {"$ref": "#/d"}},
            "callbacks": {"onCreate": {"$ref": "#/e"}}
        }
        JSON;

    SmokeTest::assertJson($expected, $components);
};

$smoke->run("Components", $testComponents);
