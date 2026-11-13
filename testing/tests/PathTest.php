<?php

/*
 * Smoke tests for Operation, OperationCollection, PathItem, Path, PathCollection, Callback, NamedCallback and
 * CallbackMap. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Callback;
use Programster\Swagger\CallbackMap;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidDocumentException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Info;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedCallback;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\References\CallbackReference;
use Programster\Swagger\References\ParameterReference;
use Programster\Swagger\References\PathItemReference;
use Programster\Swagger\References\RequestBodyReference;
use Programster\Swagger\RequestBody;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createOkResponses = function () : ResponseCollection {
    return new ResponseCollection(new MappedResponse(ResponseCode::OK, new Response("OK.")));
};

$createIdParameter = function () : Parameter {
    return Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString());
};

$testOperation = function () use ($createOkResponses) : void {
    $operation = new Operation(Method::GET, $createOkResponses());
    assert($operation->getMethod() === Method::GET);
    assert($operation->getOperationId() === null);
    assert($operation->getSecurity() === null);

    // The method isn't output, as it is the operation's key in its PathItem.
    SmokeTest::assertJson('{"responses":{"200":{"description":"OK."}}}', $operation);

    $fullOperation = new Operation(
        method: Method::POST,
        responses: $createOkResponses(),
        summary: "Create a user.",
        description: "Creates a user.",
        parameters: new ParameterCollection(ParameterReference::createToComponent("tenant")),
        requestBody: RequestBodyReference::createToComponent("User"),
        tags: ["users", "users", "admin"],
        operationId: "createUser",
        externalDocs: new ExternalDocumentation("https://docs.example.com/users"),
        callbacks: new CallbackMap(new NamedCallback("onCreate", CallbackReference::createToComponent("UserCreated"))),
        deprecated: true,
        security: new SecurityRequirementCollection(),
        servers: new ServerCollection(new Server("https://api.example.com")),
    );

    // Empty security means the operation is public, so it's output as [], rather than left out.
    $expected = <<<'JSON'
        {
            "tags": ["users", "admin"],
            "summary": "Create a user.",
            "description": "Creates a user.",
            "externalDocs": {"url": "https://docs.example.com/users"},
            "operationId": "createUser",
            "parameters": [{"$ref": "#/components/parameters/tenant"}],
            "requestBody": {"$ref": "#/components/requestBodies/User"},
            "responses": {"200": {"description": "OK."}},
            "callbacks": {"onCreate": {"$ref": "#/components/callbacks/UserCreated"}},
            "deprecated": true,
            "security": [],
            "servers": [{"url": "https://api.example.com"}]
        }
        JSON;

    SmokeTest::assertJson($expected, $fullOperation);
    assert($fullOperation->getOperationId() === "createUser");
    assert(count($fullOperation->getSecurity()) === 0);
    assert(count($fullOperation->getParameters()) === 1);
};

$smoke->run("Operation", $testOperation);

$testOperationRules = function () use ($createOkResponses) : void {
    $body = new RequestBody(new ContentMap(new MediaType(MimeType::JSON)));

    foreach ([Method::GET, Method::HEAD, Method::DELETE, Method::TRACE] as $method)
    {
        $createWithBody = function () use ($method, $createOkResponses, $body) : void {
            new Operation($method, $createOkResponses(), requestBody: $body);
        };

        SmokeTest::expectException(InvalidValueException::class, $createWithBody, "can't have a request body");
    }

    foreach ([Method::POST, Method::PUT, Method::PATCH, Method::OPTIONS] as $method)
    {
        new Operation($method, $createOkResponses(), requestBody: $body);
    }

    $createWithNumericTag = function () use ($createOkResponses) : void {
        new Operation(Method::GET, $createOkResponses(), tags: ["users", 5]);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithNumericTag, "one was a int");

    // The responses can be emptied after they are created, so the operation checks too.
    $createWithEmptiedResponses = function () use ($createOkResponses) : void {
        $responses = $createOkResponses();
        $responses->clear();
        new Operation(Method::GET, $responses);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithEmptiedResponses,
        "needs at least one response"
    );
};

$smoke->run("Operation rules", $testOperationRules);

$testOperationCollection = function () use ($createOkResponses) : void {
    $collection = new OperationCollection(new Operation(Method::GET, $createOkResponses()));
    $collection->add(new Operation(Method::POST, $createOkResponses()));
    assert($collection->getKeys() === ["get", "post"]);

    $addDuplicate = function () use ($collection, $createOkResponses) : void {
        $collection->add(new Operation(Method::GET, $createOkResponses()));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $addDuplicate, "already has a GET operation");
};

$smoke->run("OperationCollection", $testOperationCollection);

$testPathItem = function () use ($createOkResponses, $createIdParameter) : void {
    SmokeTest::assertJson('{}', new PathItem());

    $item = new PathItem(
        operations: new OperationCollection(
            new Operation(Method::GET, $createOkResponses()),
            new Operation(Method::DELETE, $createOkResponses()),
        ),
        summary: "A user.",
        description: "One user.",
        servers: new ServerCollection(new Server("https://api.example.com")),
        parameters: new ParameterCollection($createIdParameter()),
    );

    $expected = <<<'JSON'
        {
            "summary": "A user.",
            "description": "One user.",
            "get": {"responses": {"200": {"description": "OK."}}},
            "delete": {"responses": {"200": {"description": "OK."}}},
            "servers": [{"url": "https://api.example.com"}],
            "parameters": [{"name": "id", "in": "path", "required": true, "schema": {"type": "string"}}]
        }
        JSON;

    SmokeTest::assertJson($expected, $item);
    assert(count($item->getOperations()) === 2 && count($item->getParameters()) === 1);
};

$smoke->run("PathItem", $testPathItem);

$testPath = function () use ($createOkResponses, $createIdParameter) : void {
    $operations = new OperationCollection(new Operation(Method::GET, $createOkResponses()));
    $item = new PathItem($operations, parameters: new ParameterCollection($createIdParameter()));
    $path = new Path("/users/{id}", $item);
    assert($path->getRelativePath() === "/users/{id}");
    assert($path->getItem() === $item);

    // The path parameter can be the operation's own, instead of the item's.
    $ownParameter = new Operation(
        Method::GET,
        $createOkResponses(),
        parameters: new ParameterCollection($createIdParameter())
    );

    new Path("/users/{id}", new PathItem(new OperationCollection($ownParameter)));

    // A reference could be to the path parameter, so it can't be said to be missing.
    $byReference = new Operation(
        Method::GET,
        $createOkResponses(),
        parameters: new ParameterCollection(ParameterReference::createToComponent("userId"))
    );

    new Path("/users/{id}", new PathItem(new OperationCollection($byReference)));

    $createWithoutSlash = function () : void {
        new Path("users", new PathItem());
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithoutSlash, "must start with a \"/\"");

    $createWithMissingParameter = function () use ($operations) : void {
        new Path("/users/{id}", new PathItem($operations));
    };

    SmokeTest::expectException(
        InvalidDocumentException::class,
        $createWithMissingParameter,
        'GET /users/{id} has no path parameter named "id"'
    );

    $createWithUnusedParameter = function () use ($item) : void {
        new Path("/users", $item);
    };

    SmokeTest::expectException(
        InvalidDocumentException::class,
        $createWithUnusedParameter,
        'has a path parameter named "id", but the path has no {id}'
    );
};

$smoke->run("Path", $testPath);

$testPathCollection = function () use ($createOkResponses, $createIdParameter) : void {
    $byId = new PathItem(
        new OperationCollection(new Operation(Method::GET, $createOkResponses())),
        parameters: new ParameterCollection($createIdParameter())
    );

    $collection = new PathCollection(new Path("/users", new PathItem()), new Path("/users/{id}", $byId));
    SmokeTest::assertJson('{"/users":{},"/users/{id}":' . json_encode($byId) . '}', $collection);

    // Paths that only differ in their variables' names are the same path.
    $name = Parameter::createFromSchema("name", ParameterLocation::PATH, new SchemaString());

    $byName = new PathItem(
        new OperationCollection(new Operation(Method::GET, $createOkResponses())),
        parameters: new ParameterCollection($name)
    );

    $addSamePath = function () use ($collection, $byName) : void {
        $collection->add(new Path("/users/{name}", $byName));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $addSamePath, 'a path that matches "/users/{}"');
};

$smoke->run("PathCollection", $testPathCollection);

$testCallbacks = function () use ($createOkResponses) : void {
    $item = new PathItem(new OperationCollection(new Operation(Method::POST, $createOkResponses())));
    $callback = new Callback('{$request.body#/callbackUrl}', $item);
    $expected = '{"{$request.body#/callbackUrl}":{"post":{"responses":{"200":{"description":"OK."}}}}}';
    SmokeTest::assertJson($expected, $callback);

    $named = new NamedCallback("UserCreated", $callback);
    assert($named->getName() === "UserCreated" && $named->getCallback() === $callback);
    SmokeTest::assertJson('{"$ref":"#/components/callbacks/UserCreated"}', $named->createReference());
    SmokeTest::assertJson('{"UserCreated":' . $expected . '}', new CallbackMap($named));

    $createWithDuplicateNames = function () use ($named) : void {
        new CallbackMap($named, $named);
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $createWithDuplicateNames,
        'a callback named "UserCreated"'
    );

    $createWithoutExpression = function () use ($item) : void {
        new Callback("", $item);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithoutExpression,
        "expression can't be an empty string"
    );
};

$smoke->run("Callback, NamedCallback and CallbackMap", $testCallbacks);

$testPathItemReferences = function () : void {
    $shared = PathItemReference::createToDocument("shared/health.json");

    // The path's variables can't be checked against parameters that are in another document.
    $path = new Path("/health/{check}", $shared);
    assert($path->getItem() === $shared);
    SmokeTest::assertJson('{"/health/{check}":{"$ref":"shared/health.json"}}', new PathCollection($path));

    $callback = new Callback('{$request.body#/url}', $shared);
    SmokeTest::assertJson('{"{$request.body#/url}":{"$ref":"shared/health.json"}}', $callback);

    // The document skips the operations it can't see.
    $document = new OpenApi(new Info("API", "1.0"), new PathCollection($path));
    SmokeTest::assertJson('{"/health/{check}":{"$ref":"shared/health.json"}}', $document->jsonSerialize()->paths);
};

$smoke->run("Paths and callbacks with path item references", $testPathItemReferences);
