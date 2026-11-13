<?php

/*
 * Smoke tests for RequestBody, NamedRequestBody and RequestBodyMap. These are run by testing/main.php, which provides
 * $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedRequestBody;
use Programster\Swagger\RequestBody;
use Programster\Swagger\RequestBodyMap;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testRequestBody = function () : void {
    $content = new ContentMap(new MediaType(MimeType::JSON, new SchemaString()));
    SmokeTest::assertJson('{"content":{"application/json":{"schema":{"type":"string"}}}}', new RequestBody($content));

    $body = new RequestBody($content, "The user to create.", true);

    $expected = '{"description":"The user to create.","content":{"application/json":{"schema":{"type":"string"}}},'
        . '"required":true}';

    SmokeTest::assertJson($expected, $body);

    // The body keeps a copy of the content, so it can't be emptied afterwards.
    $content->clear();
    SmokeTest::assertJson($expected, $body);

    $createWithEmptyContent = function () use ($content) : void {
        new RequestBody($content);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithEmptyContent, "content can't be empty");
};

$smoke->run("RequestBody", $testRequestBody);

$testRequestBodyMap = function () : void {
    $body = new RequestBody(new ContentMap(new MediaType(MimeType::JSON)));
    $named = new NamedRequestBody("User", $body);
    assert($named->getName() === "User" && $named->getRequestBody() === $body);
    SmokeTest::assertJson('{"$ref":"#/components/requestBodies/User"}', $named->createReference());

    $map = new RequestBodyMap($named);
    SmokeTest::assertJson('{"User":{"content":{"application/json":{}}}}', $map);

    $createWithDuplicateNames = function () use ($named) : void {
        new RequestBodyMap($named, $named);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a request body named "User"');
};

$smoke->run("RequestBodyMap and NamedRequestBody", $testRequestBodyMap);
