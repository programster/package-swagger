<?php

/*
 * Smoke tests for Response, MappedResponse, ResponseCollection, Link and LinkMap. These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Reference;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Server;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testResponse = function () : void {
    $content = new ContentMap(MimeType::JSON, new MediaType(new SchemaString()));
    $response = new Response("A user.", $content);
    $expected = '{"description":"A user.","content":{"application/json":{"schema":{"type":"string"}}}}';
    SmokeTest::assertJson($expected, $response);

    $fullResponse = new Response(
        "A user.",
        $content,
        new HeaderMap(Header::createFromSchema("X-Rate-Limit", new SchemaString())),
        new LinkMap("group", new Reference("#/links/group"))
    );

    $expected = <<<'JSON'
        {
            "description": "A user.",
            "content": {"application/json": {"schema": {"type": "string"}}},
            "headers": {"X-Rate-Limit": {"schema": {"type": "string"}}},
            "links": {"group": {"$ref": "#/links/group"}}
        }
        JSON;

    SmokeTest::assertJson($expected, $fullResponse);
    SmokeTest::assertJson($expected, $fullResponse->toArray());
};

$smoke->run("Response", $testResponse);

$testResponseCollection = function () : void {
    $notFound = new Reference("#/components/responses/NotFound");
    $mapped = new MappedResponse(ResponseCode::NOT_FOUND, $notFound);
    assert($mapped->getResponseCode() === ResponseCode::NOT_FOUND);
    assert($mapped->getResponse() === $notFound);

    $collection = new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Reference("#/components/responses/Ok")),
        $mapped,
        new MappedResponse(ResponseCode::DEFAULT, new Reference("#/components/responses/Error")),
    );

    $expected = <<<'JSON'
        {
            "200": {"$ref": "#/components/responses/Ok"},
            "404": {"$ref": "#/components/responses/NotFound"},
            "default": {"$ref": "#/components/responses/Error"}
        }
        JSON;

    SmokeTest::assertJson($expected, $collection);
    assert(count($collection->toArray()) === 3);

    $createWithDuplicateCodes = function () use ($mapped) : void {
        new ResponseCollection($mapped, $mapped);
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateCodes, "Overlapping response code: 404");
};

$smoke->run("ResponseCollection and MappedResponse", $testResponseCollection);

$testLink = function () : void {
    $link = new Link(
        operationRef: null,
        operationId: "getGroup",
        parameters: ['id' => '$response.body#/groupId'],
        requestBody: null,
        description: "The user's group.",
        server: new Server("https://api.example.com", null, null)
    );

    $expected = <<<'JSON'
        {
            "operationId": "getGroup",
            "parameters": {"id": "$response.body#/groupId"},
            "description": "The user's group.",
            "server": {"url": "https://api.example.com"}
        }
        JSON;

    SmokeTest::assertJson($expected, $link);

    $byRef = new Link("#/paths/~1groups/get", null, null, ['name' => "x"], null, null);
    SmokeTest::assertJson('{"operationRef":"#/paths/~1groups/get","requestBody":{"name":"x"}}', $byRef);
};

$smoke->run("Link", $testLink);

$testLinkMap = function () : void {
    $map = new LinkMap("group", new Reference("#/a"));
    $map->add("owner", new Reference("#/b"));
    $map->addOrReplace("owner", new Reference("#/c"));
    SmokeTest::assertJson('{"group":{"$ref":"#/a"},"owner":{"$ref":"#/c"}}', $map);
    assert(array_keys($map->toArray()) === ["group", "owner"]);

    $addDuplicate = function () use ($map) : void {
        $map->add("group", new Reference("#/d"));
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "group already taken");
};

$smoke->run("LinkMap", $testLinkMap);
