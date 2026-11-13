<?php

/*
 * Smoke tests for Response, MappedResponse, ResponseCollection, NamedResponse, ResponseMap, Link, NamedLink and
 * LinkMap. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedLink;
use Programster\Swagger\NamedResponse;
use Programster\Swagger\References\LinkReference;
use Programster\Swagger\References\ResponseReference;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\ResponseMap;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Server;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testResponse = function () : void {
    // A response doesn't need any content, e.g. a 204.
    SmokeTest::assertJson('{"description":"Deleted."}', new Response("Deleted."));

    $fullResponse = new Response(
        "A user.",
        new ContentMap(new MediaType(MimeType::JSON, new SchemaString())),
        new HeaderCollection(new MappedHeader("X-Rate-Limit", Header::createFromSchema(new SchemaString()))),
        new LinkMap(new NamedLink("group", LinkReference::createToComponent("UserGroup")))
    );

    $expected = <<<'JSON'
        {
            "description": "A user.",
            "headers": {"X-Rate-Limit": {"schema": {"type": "string"}}},
            "content": {"application/json": {"schema": {"type": "string"}}},
            "links": {"group": {"$ref": "#/components/links/UserGroup"}}
        }
        JSON;

    SmokeTest::assertJson($expected, $fullResponse);

    $createWithoutDescription = function () : void {
        new Response("");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithoutDescription,
        "description can't be an empty string"
    );

    $createWithContentTypeHeader = function () : void {
        $contentType = new MappedHeader("content-type", Header::createFromSchema(new SchemaString()));
        new Response("A user.", headers: new HeaderCollection($contentType));
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithContentTypeHeader,
        "can't include Content-Type"
    );
};

$smoke->run("Response", $testResponse);

$testResponseCollection = function () : void {
    $notFound = ResponseReference::createToComponent("NotFound");
    $mapped = new MappedResponse(ResponseCode::NOT_FOUND, $notFound);
    assert($mapped->getCode() === "404");
    assert($mapped->getResponse() === $notFound);

    $collection = new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response("OK.")),
        $mapped,
        new MappedResponse(ResponseCode::DEFAULT, ResponseReference::createToComponent("Error")),
    );

    $expected = <<<'JSON'
        {
            "200": {"description": "OK."},
            "404": {"$ref": "#/components/responses/NotFound"},
            "default": {"$ref": "#/components/responses/Error"}
        }
        JSON;

    SmokeTest::assertJson($expected, $collection);
    assert($collection->getKeys() === ["200", "404", "default"]);

    $createWithDuplicateCodes = function () use ($mapped) : void {
        new ResponseCollection($mapped, $mapped);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateCodes, "a response for the 404 code");
};

$smoke->run("ResponseCollection and MappedResponse", $testResponseCollection);

$testResponseMap = function () : void {
    $response = new Response("Not found.");
    $named = new NamedResponse("NotFound", $response);
    assert($named->getName() === "NotFound" && $named->getResponse() === $response);
    SmokeTest::assertJson('{"$ref":"#/components/responses/NotFound"}', $named->createReference());
    SmokeTest::assertJson('{"NotFound":{"description":"Not found."}}', new ResponseMap($named));

    $createWithDuplicateNames = function () use ($named) : void {
        new ResponseMap($named, $named);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a response named "NotFound"');
};

$smoke->run("ResponseMap and NamedResponse", $testResponseMap);

$testLink = function () : void {
    $link = Link::createFromOperationId(
        operationId: "getGroup",
        parameters: ['groupId' => '$response.body#/groupId'],
        description: "The user's group.",
        server: new Server("https://api.example.com")
    );

    $expected = <<<'JSON'
        {
            "operationId": "getGroup",
            "parameters": {"groupId": "$response.body#/groupId"},
            "description": "The user's group.",
            "server": {"url": "https://api.example.com"}
        }
        JSON;

    SmokeTest::assertJson($expected, $link);

    $byRef = Link::createFromOperationRef("#/paths/~1groups/post", requestBody: ['name' => '$request.body#/name']);

    SmokeTest::assertJson(
        '{"operationRef":"#/paths/~1groups/post","requestBody":{"name":"$request.body#/name"}}',
        $byRef
    );

    $createWithoutOperation = function () : void {
        Link::createFromOperationId("");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithoutOperation,
        "operationId can't be an empty string"
    );
};

$smoke->run("Link", $testLink);

$testLinkMap = function () : void {
    $link = Link::createFromOperationId("getGroup");
    $named = new NamedLink("UserGroup", $link);
    assert($named->getName() === "UserGroup" && $named->getLink() === $link);
    SmokeTest::assertJson('{"$ref":"#/components/links/UserGroup"}', $named->createReference());
    SmokeTest::assertJson('{"UserGroup":{"operationId":"getGroup"}}', new LinkMap($named));

    $createWithDuplicateNames = function () use ($named) : void {
        new LinkMap($named, $named);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a link named "UserGroup"');
};

$smoke->run("LinkMap and NamedLink", $testLinkMap);

$testOtherResponseCodes = function () : void {
    $collection = new ResponseCollection(
        new MappedResponse(ResponseCode::ANY_SUCCESS, new Response("OK.")),
        new MappedResponse(ResponseCode::ANY_CLIENT_ERROR, new Response("Bad request.")),
        new MappedResponse(ResponseCode::CONTINUE, new Response("Continue.")),
        new MappedResponse(499, new Response("Client closed the connection.")),
    );

    $expected = '{"2XX":{"description":"OK."},"4XX":{"description":"Bad request."},'
        . '"100":{"description":"Continue."},"499":{"description":"Client closed the connection."}}';

    SmokeTest::assertJson($expected, $collection);
    assert($collection->getKeys() === ["2XX", "4XX", "100", "499"]);

    // An int that ResponseCode has is the same code, so it can't be given twice.
    $sameCodeTwice = function () : void {
        new ResponseCollection(
            new MappedResponse(404, new Response("Not found.")),
            new MappedResponse(ResponseCode::NOT_FOUND, new Response("Not found."))
        );
    };

    SmokeTest::expectException(DuplicateKeyException::class, $sameCodeTwice, "a response for the 404 code");

    foreach ([99, 600, -1] as $badCode)
    {
        $createWithBadCode = function () use ($badCode) : void {
            new MappedResponse($badCode, new Response("Odd."));
        };

        SmokeTest::expectException(InvalidValueException::class, $createWithBadCode, "{$badCode} isn't an HTTP status");
    }
};

$smoke->run("Response code ranges and ints", $testOtherResponseCodes);
