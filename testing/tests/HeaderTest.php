<?php

/*
 * Smoke tests for Header, MappedHeader, HeaderCollection, NamedHeader and HeaderMap. These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedHeader;
use Programster\Swagger\References\HeaderReference;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testHeaderFromSchema = function () : void {
    SmokeTest::assertJson('{"schema":{"type":"string"}}', Header::createFromSchema(new SchemaString()));

    $fullHeader = Header::createFromSchema(
        schema: new SchemaString(),
        required: true,
        description: "Requests left.",
        deprecated: true,
        explode: false,
        example: new ValueExample("100"),
        extensions: new ExtensionMap(new Extension("x-internal", false)),
    );

    $expected = <<<'JSON'
        {
            "description": "Requests left.",
            "required": true,
            "deprecated": true,
            "explode": false,
            "schema": {"type": "string"},
            "example": "100",
            "x-internal": false
        }
        JSON;

    SmokeTest::assertJson($expected, $fullHeader);

    $examples = new ExampleMap(new NamedExample("plenty", new ValueExample("100")));
    $withExamples = Header::createFromSchema(new SchemaString(), example: $examples);
    SmokeTest::assertJson('{"schema":{"type":"string"},"examples":{"plenty":{"value":"100"}}}', $withExamples);
};

$smoke->run("Header::createFromSchema()", $testHeaderFromSchema);

$testHeaderFromContentMap = function () : void {
    $content = new ContentMap(new MediaType(MimeType::JSON, new SchemaString()));
    $header = Header::createFromContentMap($content, true, "Some data.", true);

    $expected = '{"description":"Some data.","required":true,"deprecated":true,'
        . '"content":{"application/json":{"schema":{"type":"string"}}}}';

    SmokeTest::assertJson($expected, $header);

    $createWithTwoMediaTypes = function () : void {
        $content = new ContentMap(new MediaType(MimeType::JSON), new MediaType(MimeType::XML));
        Header::createFromContentMap($content);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithTwoMediaTypes,
        "must have exactly one media type"
    );
};

$smoke->run("Header::createFromContentMap()", $testHeaderFromContentMap);

$testHeaderCollection = function () : void {
    $mapped = new MappedHeader("X-A", Header::createFromSchema(new SchemaString()));
    assert($mapped->getName() === "X-A");

    $collection = new HeaderCollection(
        $mapped,
        new MappedHeader("X-B", Header::createFromSchema(new SchemaString(), required: true)),
        new MappedHeader("X-Rate-Limit", HeaderReference::createToComponent("RateLimit")),
    );

    $expected = '{"X-A":{"schema":{"type":"string"}},"X-B":{"required":true,"schema":{"type":"string"}},'
        . '"X-Rate-Limit":{"$ref":"#/components/headers/RateLimit"}}';

    SmokeTest::assertJson($expected, $collection);
    assert($collection->getKeys() === ["X-A", "X-B", "X-Rate-Limit"]);

    $createWithDuplicateNames = function () use ($mapped) : void {
        new HeaderCollection($mapped, $mapped);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a header named "X-A"');

    $createWithBadName = function () : void {
        new MappedHeader("X Rate Limit", Header::createFromSchema(new SchemaString()));
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadName, "isn't a valid HTTP header name");
};

$smoke->run("MappedHeader and HeaderCollection", $testHeaderCollection);

$testHeaderMap = function () : void {
    $header = Header::createFromSchema(new SchemaString());
    $named = new NamedHeader("RateLimit", $header);
    assert($named->getName() === "RateLimit" && $named->getHeader() === $header);
    SmokeTest::assertJson('{"$ref":"#/components/headers/RateLimit"}', $named->createReference());

    $map = new HeaderMap($named, new NamedHeader("Other", HeaderReference::createToDocument("headers.json")));
    SmokeTest::assertJson('{"RateLimit":{"schema":{"type":"string"}},"Other":{"$ref":"headers.json"}}', $map);

    $createWithDuplicateNames = function () use ($named) : void {
        new HeaderMap($named, $named);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a header named "RateLimit"');

    $createWithBadName = function () use ($header) : void {
        new NamedHeader("Rate Limit", $header);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadName, "can only contain letters, numbers");
};

$smoke->run("NamedHeader and HeaderMap", $testHeaderMap);
