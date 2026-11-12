<?php

/*
 * Smoke tests for Header and HeaderMap. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Reference;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testHeaderFromSchema = function () : void {
    $header = Header::createFromSchema("Authorization", new SchemaString());
    assert($header->getName() === "Authorization");

    // The name isn't output, as it is the header's key in a HeaderMap.
    SmokeTest::assertJson('{"schema":{"type":"string"}}', $header);
    SmokeTest::assertJson('{"schema":{"$ref":"#/a"}}', Header::createFromSchema("A", new Reference("#/a")));

    $fullHeader = Header::createFromSchema(
        name: "Authorization",
        schema: new SchemaString(),
        required: true,
        description: "The auth header.",
        deprecated: false,
        allowEmptyValue: false,
        explode: true,
        allowReserved: false,
        example: new ValueExample("Bearer abc"),
    );

    $expected = <<<'JSON'
        {
            "required": true,
            "description": "The auth header.",
            "deprecated": false,
            "allowEmptyValue": false,
            "schema": {"type": "string"},
            "explode": true,
            "allowReserved": false,
            "example": "Bearer abc"
        }
        JSON;

    SmokeTest::assertJson($expected, $fullHeader);
    SmokeTest::assertJson($expected, $fullHeader->toArray());

    // This used to crash, as the map was converted to an array before being passed on.
    $examples = new ExampleMap(new NamedExample("bearer", new ValueExample("Bearer abc")));
    $withExamples = Header::createFromSchema("Authorization", new SchemaString(), example: $examples);
    SmokeTest::assertJson('{"schema":{"type":"string"},"examples":{"bearer":{"value":"Bearer abc"}}}', $withExamples);
};

$smoke->run("Header::createFromSchema()", $testHeaderFromSchema);

$testHeaderFromContentMap = function () : void {
    $content = new ContentMap(MimeType::JSON, new MediaType(new SchemaString()));
    $header = Header::createFromContentMap("X-Data", $content, true, "Some data.", true, false);
    assert($header->getName() === "X-Data");

    $expected = <<<'JSON'
        {
            "required": true,
            "description": "Some data.",
            "deprecated": true,
            "allowEmptyValue": false,
            "content": {"application/json": {"schema": {"type": "string"}}}
        }
        JSON;

    SmokeTest::assertJson($expected, $header);
};

$smoke->run("Header::createFromContentMap()", $testHeaderFromContentMap);

$testHeaderMap = function () : void {
    $map = new HeaderMap(
        Header::createFromSchema("X-A", new SchemaString()),
        Header::createFromSchema("X-B", new SchemaString(), required: true),
    );

    $expected = '{"X-A":{"schema":{"type":"string"}},"X-B":{"required":true,"schema":{"type":"string"}}}';
    SmokeTest::assertJson($expected, $map);
    assert(array_keys($map->toArray()) === ["X-A", "X-B"]);
    assert($map->toArray()["X-A"] instanceof Header);

    $createWithDuplicateNames = function () : void {
        $header = Header::createFromSchema("X-A", new SchemaString());
        new HeaderMap($header, Header::createFromSchema("X-A", new SchemaString()));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "Duplicate header: X-A");
};

$smoke->run("HeaderMap", $testHeaderMap);
