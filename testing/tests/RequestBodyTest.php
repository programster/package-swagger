<?php

/*
 * Smoke tests for RequestBody and RequestBodyMap. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Reference;
use Programster\Swagger\RequestBody;
use Programster\Swagger\RequestBodyMap;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testRequestBody = function () : void {
    $content = new ContentMap(MimeType::JSON, new MediaType(new SchemaString()));
    SmokeTest::assertJson('{"content":{"application/json":{"schema":{"type":"string"}}}}', new RequestBody($content));

    $body = new RequestBody($content, "The user to create.", true);

    $expected = '{"content":{"application/json":{"schema":{"type":"string"}}},"required":true,'
        . '"description":"The user to create."}';

    SmokeTest::assertJson($expected, $body);
};

$smoke->run("RequestBody", $testRequestBody);

$testRequestBodyMap = function () : void {
    $map = new RequestBodyMap("user", new Reference("#/a"));
    $map->add("group", new Reference("#/b"));
    $map->addOrReplace("group", new Reference("#/c"));
    SmokeTest::assertJson('{"user":{"$ref":"#/a"},"group":{"$ref":"#/c"}}', $map);
    assert(array_keys($map->toArray()) === ["user", "group"]);

    // add() used to check the wrong property, so it never caught a duplicate name.
    $addDuplicate = function () use ($map) : void {
        $map->add("user", new Reference("#/d"));
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "user already taken");
};

$smoke->run("RequestBodyMap", $testRequestBodyMap);
