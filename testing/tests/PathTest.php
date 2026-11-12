<?php

/*
 * Smoke tests for Path, PathCollection, Operation, OperationCollection, Callback, NamedCallback and Callbacks. These
 * are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Callback;
use Programster\Swagger\Callbacks;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\Method;
use Programster\Swagger\NamedCallback;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\Reference;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createOkResponses = function () : ResponseCollection {
    return new ResponseCollection(new MappedResponse(ResponseCode::OK, new Reference("#/responses/Ok")));
};

$testOperation = function () use ($createOkResponses) : void {
    $operation = new Operation(Method::GET, $createOkResponses());
    assert($operation->getMethod() === Method::GET);

    // The method isn't output, as it is the operation's key in its Path.
    SmokeTest::assertJson('{"responses":{"200":{"$ref":"#/responses/Ok"}}}', $operation);

    $fullOperation = new Operation(
        method: Method::POST,
        responses: $createOkResponses(),
        summary: "Create a user.",
        description: "Creates a user.",
        parameters: new ParameterCollection(new Reference("#/parameters/a")),
        requestBody: new Reference("#/requestBodies/User"),
        tags: ["users"],
        operationId: "createUser",
        externalDocs: "https://docs.example.com",
        callbacks: new Callbacks(new NamedCallback("onCreate", new Reference("#/callbacks/a"))),
        deprecated: true,
        security: new SecurityRequirementCollection(new SecurityRequirement("apiKey", [])),
        servers: new ServerCollection(new Server("https://api.example.com", null, null)),
    );

    $expected = <<<'JSON'
        {
            "responses": {"200": {"$ref": "#/responses/Ok"}},
            "summary": "Create a user.",
            "description": "Creates a user.",
            "parameters": [{"$ref": "#/parameters/a"}],
            "requestBody": {"$ref": "#/requestBodies/User"},
            "tags": ["users"],
            "operationId": "createUser",
            "externalDocs": "https://docs.example.com",
            "callbacks": {"onCreate": {"$ref": "#/callbacks/a"}},
            "deprecated": true,
            "security": [{"apiKey": []}],
            "servers": [{"url": "https://api.example.com"}]
        }
        JSON;

    SmokeTest::assertJson($expected, $fullOperation);

    $createWithoutResponses = function () : void {
        new Operation(Method::GET, new ResponseCollection());
    };

    SmokeTest::expectException(\Exception::class, $createWithoutResponses, "need at least one response");

    $createWithNumericTag = function () use ($createOkResponses) : void {
        new Operation(Method::GET, $createOkResponses(), tags: ["users", 5]);
    };

    SmokeTest::expectException(\Exception::class, $createWithNumericTag, "one of them is of type int");
};

$smoke->run("Operation", $testOperation);

$testOperationCollection = function () use ($createOkResponses) : void {
    $collection = new OperationCollection(new Operation(Method::GET, $createOkResponses()));
    $collection->append(new Operation(Method::POST, $createOkResponses()));
    $collection[] = new Operation(Method::PUT, $createOkResponses());
    assert(count($collection) === 3);
    assert($collection->jsonSerialize()[2]->getMethod() === Method::PUT);

    // These used to fail with "Class not found", as the exception class wasn't fully qualified.
    $appendString = function () use ($collection) : void {
        $collection->append("not an operation");
    };

    SmokeTest::expectException(\Exception::class, $appendString, "Cannot append non Operation");

    $setString = function () use ($collection) : void {
        $collection[0] = "not an operation";
    };

    SmokeTest::expectException(\Exception::class, $setString, "Cannot add a non Operation value");
};

$smoke->run("OperationCollection", $testOperationCollection);

$testPath = function () use ($createOkResponses) : void {
    $operations = new OperationCollection(
        new Operation(Method::GET, $createOkResponses()),
        new Operation(Method::DELETE, $createOkResponses()),
    );

    $path = new Path("/users", $operations);
    assert($path->getRelativePath() === "/users");

    $expected = <<<'JSON'
        {
            "get": {"responses": {"200": {"$ref": "#/responses/Ok"}}},
            "delete": {"responses": {"200": {"$ref": "#/responses/Ok"}}}
        }
        JSON;

    SmokeTest::assertJson($expected, $path);

    $fullPath = new Path(
        relativePath: "/users",
        operations: new OperationCollection(new Operation(Method::GET, $createOkResponses())),
        ref: "users.json",
        summary: "Users.",
        description: "Managing users.",
        servers: new ServerCollection(new Server("https://api.example.com", null, null)),
        parameters: new ParameterCollection(new Reference("#/parameters/a")),
    );

    // The reference used to be output as "#ref", rather than "$ref".
    $expected = <<<'JSON'
        {
            "$ref": "users.json",
            "summary": "Users.",
            "description": "Managing users.",
            "servers": [{"url": "https://api.example.com"}],
            "parameters": [{"$ref": "#/parameters/a"}],
            "get": {"responses": {"200": {"$ref": "#/responses/Ok"}}}
        }
        JSON;

    SmokeTest::assertJson($expected, $fullPath);
    SmokeTest::assertJson($expected, $fullPath->toArray());

    // A path without any operations used to raise a warning, from looping over null.
    SmokeTest::assertJson('{"summary":"Users."}', new Path("/users", null, summary: "Users."));
};

$smoke->run("Path", $testPath);

$testPathCollection = function () : void {
    $collection = new PathCollection(new Path("/users", null));
    $collection->append(new Path("/groups", null));
    $collection[] = new Path("/roles", null);
    assert(count($collection) === 3);
    assert($collection->jsonSerialize()[1]->getRelativePath() === "/groups");

    $appendString = function () use ($collection) : void {
        $collection->append("/not-a-path");
    };

    SmokeTest::expectException(\Exception::class, $appendString, "Cannot append non Path");

    $setString = function () use ($collection) : void {
        $collection[0] = "/not-a-path";
    };

    SmokeTest::expectException(\Exception::class, $setString, "Cannot add a non Path value");
};

$smoke->run("PathCollection", $testPathCollection);

$testCallbacks = function () use ($createOkResponses) : void {
    $path = new Path("/hook", new OperationCollection(new Operation(Method::POST, $createOkResponses())));
    $callback = new Callback('{$request.body#/callbackUrl}', $path);
    $expected = '{"{$request.body#/callbackUrl}":{"post":{"responses":{"200":{"$ref":"#/responses/Ok"}}}}}';
    SmokeTest::assertJson($expected, $callback);

    $named = new NamedCallback("onCreate", $callback);
    assert($named->getName() === "onCreate");
    assert($named->getCallback() === $callback);

    $callbacks = new Callbacks($named, new NamedCallback("onDelete", new Reference("#/callbacks/onDelete")));

    $expected = '{"onCreate":{"{$request.body#/callbackUrl}":{"post":{"responses":{"200":{"$ref":"#/responses/Ok"}}}}},'
        . '"onDelete":{"$ref":"#/callbacks/onDelete"}}';

    SmokeTest::assertJson($expected, $callbacks);

    $createWithDuplicateNames = function () use ($named) : void {
        new Callbacks($named, $named);
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "same name: onCreate");
};

$smoke->run("Callback, NamedCallback and Callbacks", $testCallbacks);
