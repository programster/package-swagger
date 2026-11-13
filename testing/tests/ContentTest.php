<?php

/*
 * Smoke tests for the content of requests and responses: MediaType, ContentMap, Encoding and EncodingMap. These are
 * run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Encoding;
use Programster\Swagger\EncodingMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;
use Programster\Swagger\Style;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testMediaType = function () : void {
    $mediaType = new MediaType(MimeType::JSON, new SchemaString());
    assert($mediaType->getMimeType() === "application/json");
    SmokeTest::assertJson('{"schema":{"type":"string"}}', $mediaType);
    SmokeTest::assertJson('{}', new MediaType(MimeType::BINARY));

    SmokeTest::assertJson(
        '{"schema":{"$ref":"#/components/schemas/A"}}',
        new MediaType(MimeType::JSON, SchemaReference::createToComponent("A"))
    );

    // A mime type that the enum doesn't have can be given as a string.
    $custom = new MediaType("application/vnd.api+json; charset=utf-8");
    assert($custom->getMimeType() === "application/vnd.api+json; charset=utf-8");

    $createWithBadMimeType = function () : void {
        new MediaType("json");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadMimeType, '"json" isn\'t a mime type');

    $withValue = new MediaType(MimeType::PLAIN_TEXT, new SchemaString(), new ValueExample("hello"));
    SmokeTest::assertJson('{"schema":{"type":"string"},"example":"hello"}', $withValue);

    // A map of named examples goes under "examples", rather than "example".
    $examples = new ExampleMap(new NamedExample("a", new ValueExample("hello")));
    $withMap = new MediaType(MimeType::PLAIN_TEXT, new SchemaString(), $examples);
    SmokeTest::assertJson('{"schema":{"type":"string"},"examples":{"a":{"value":"hello"}}}', $withMap);
};

$smoke->run("MediaType", $testMediaType);

$testMediaTypeEncoding = function () : void {
    $schema = new SchemaObject(properties: new PropertyMap(
        new Property("name", new SchemaString()),
        new Property("photo", new SchemaString(format: StringFormat::BINARY)),
    ));

    $encoding = new EncodingMap(new Encoding("photo", MimeType::PNG));
    $mediaType = new MediaType(MimeType::MULTIPART_FORM_DATA, $schema, encoding: $encoding);
    $expected = '{"photo":{"contentType":"image/png"}}';
    SmokeTest::assertJson($expected, $mediaType->jsonSerialize()->encoding);

    $encodeJson = function () use ($schema, $encoding) : void {
        new MediaType(MimeType::JSON, $schema, encoding: $encoding);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $encodeJson,
        "can only be given for multipart or application/x-www-form-urlencoded"
    );

    $encodeMissingProperty = function () use ($schema) : void {
        new MediaType(MimeType::FORM_URL_ENCODED, $schema, encoding: new EncodingMap(new Encoding("age")));
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $encodeMissingProperty,
        'encoding for the "age" property, but the schema'
    );
};

$smoke->run("MediaType encoding", $testMediaTypeEncoding);

$testContentMap = function () : void {
    $map = new ContentMap(new MediaType(MimeType::JSON, new SchemaString()), new MediaType("application/xml"));
    SmokeTest::assertJson('{"application/json":{"schema":{"type":"string"}},"application/xml":{}}', $map);
    assert($map->getKeys() === ["application/json", "application/xml"]);

    // The enum and a string for the same mime type are the same key.
    $createWithDuplicate = function () : void {
        new ContentMap(new MediaType(MimeType::JSON), new MediaType("application/json"));
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $createWithDuplicate,
        'a media type for "application/json"'
    );
};

$smoke->run("ContentMap", $testContentMap);

$testEncoding = function () : void {
    assert((new Encoding("photo"))->getPropertyName() === "photo");
    SmokeTest::assertJson('{}', new Encoding("photo"));

    $headers = new HeaderCollection(new MappedHeader("X-Rate-Limit", Header::createFromSchema(new SchemaInt())));
    $encoding = new Encoding("tags", MimeType::PLAIN_TEXT, $headers, Style::DEEP_OBJECT, true, true);

    $expected = <<<'JSON'
        {
            "contentType": "text/plain",
            "headers": {"X-Rate-Limit": {"schema": {"type": "integer"}}},
            "style": "deepObject",
            "explode": true,
            "allowReserved": true
        }
        JSON;

    SmokeTest::assertJson($expected, $encoding);

    $createWithPathStyle = function () : void {
        new Encoding("tags", style: Style::MATRIX);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithPathStyle,
        "The matrix style can't be used in an encoding"
    );

    $createWithContentType = function () : void {
        $contentType = new MappedHeader("Content-Type", Header::createFromSchema(new SchemaString()));
        new Encoding("photo", headers: new HeaderCollection($contentType));
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithContentType, "can't include Content-Type");

    $createWithDuplicate = function () : void {
        new EncodingMap(new Encoding("photo"), new Encoding("photo"));
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $createWithDuplicate,
        'an encoding for the "photo" property'
    );
};

$smoke->run("Encoding and EncodingMap", $testEncoding);
