<?php

/*
 * Smoke tests for the content of requests and responses: MediaType, ContentMap and Encoding. These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Encoding;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Reference;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Style;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testMediaType = function () : void {
    SmokeTest::assertJson('{"schema":{"type":"string"}}', new MediaType(new SchemaString()));
    SmokeTest::assertJson('{"schema":{"$ref":"#/a"}}', new MediaType(new Reference("#/a")));

    $withValue = new MediaType(new SchemaString(), new ValueExample("hello"));
    SmokeTest::assertJson('{"schema":{"type":"string"},"example":"hello"}', $withValue);
    assert($withValue->toArray()['example'] instanceof ValueExample);

    $withExternal = new MediaType(new SchemaString(), new ExternalValueExample("https://example.com/a.txt"));
    $expected = '{"schema":{"type":"string"},"example":{"externalValue":"https://example.com/a.txt"}}';
    SmokeTest::assertJson($expected, $withExternal);

    // A map of named examples goes under "examples", rather than "example".
    $withMap = new MediaType(new SchemaString(), new ExampleMap(new NamedExample("a", new ValueExample("hello"))));
    SmokeTest::assertJson('{"schema":{"type":"string"},"examples":{"a":{"value":"hello"}}}', $withMap);

    $encoding = new Encoding(MimeType::PLAIN_TEXT, new HeaderMap(), Style::FORM, null, false);
    $withEncoding = new MediaType(new SchemaString(), encoding: $encoding);

    $expected = '{"schema":{"type":"string"},"encoding":{"contentType":"text/plain","headers":[],"style":"form",'
        . '"allowReserved":false}}';

    SmokeTest::assertJson($expected, $withEncoding);
};

$smoke->run("MediaType", $testMediaType);

$testContentMap = function () : void {
    $map = new ContentMap(MimeType::JSON, new MediaType(new SchemaString()));
    SmokeTest::assertJson('{"application/json":{"schema":{"type":"string"}}}', $map);

    $map->add(MimeType::PLAIN_TEXT->value, new MediaType(new SchemaString()));
    $map->addOrReplace(MimeType::JSON->value, new MediaType(new SchemaInt()));
    $expected = '{"application/json":{"schema":{"type":"integer"}},"text/plain":{"schema":{"type":"string"}}}';
    SmokeTest::assertJson($expected, $map);
    assert(array_keys($map->toArray()) === ["application/json", "text/plain"]);

    $addDuplicate = function () use ($map) : void {
        $map->add("text/plain", new MediaType(new SchemaString()));
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "text/plain already taken");
};

$smoke->run("ContentMap", $testContentMap);

$testEncoding = function () : void {
    $headers = new HeaderMap(Header::createFromSchema("X-Rate-Limit", new SchemaInt()));
    $encoding = new Encoding(MimeType::JSON, $headers, "deepObject", true, true);

    $expected = <<<'JSON'
        {
            "contentType": "application/json",
            "headers": {"X-Rate-Limit": {"schema": {"type": "integer"}}},
            "style": "deepObject",
            "explode": true,
            "allowReserved": true
        }
        JSON;

    SmokeTest::assertJson($expected, $encoding);

    $createWithBadStyle = function () : void {
        new Encoding(MimeType::JSON, new HeaderMap(), "sideways", null, false);
    };

    SmokeTest::expectException(\ValueError::class, $createWithBadStyle, "sideways");
};

$smoke->run("Encoding", $testEncoding);
