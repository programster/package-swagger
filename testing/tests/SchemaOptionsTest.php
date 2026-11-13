<?php

/*
 * Smoke tests for the schema options beyond each type's own: enum, title, example, deprecated, externalDocs, xml,
 * defaults of arrays and objects, additionalProperties, read-only and write-only properties, and SchemaAnyValue. These
 * are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Schemas\Access;
use Programster\Swagger\Schemas\Discriminator;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaAnyValue;
use Programster\Swagger\Schemas\SchemaArray;
use Programster\Swagger\Schemas\SchemaBool;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaNumber;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\Xml;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testSchemaEnums = function () : void {
    $status = new SchemaString(enum: ["active", "suspended"], default: "active", example: "suspended");
    $expected = '{"type":"string","default":"active","enum":["active","suspended"],"example":"suspended"}';
    SmokeTest::assertJson($expected, $status);

    // OpenAPI only allows null when the enum has it, so a nullable schema's enum gets it.
    $nullable = new SchemaInt(nullable: true, enum: [1, 2]);
    SmokeTest::assertJson('{"type":"integer","enum":[1,2,null],"nullable":true}', $nullable);
    SmokeTest::assertJson('{"type":"number","enum":[0.5,1]}', new SchemaNumber(enum: [0.5, 1]));

    $emptyEnum = function () : void {
        new SchemaString(enum: []);
    };

    SmokeTest::expectException(InvalidValueException::class, $emptyEnum, "An enum can't be empty");

    $wrongType = function () : void {
        new SchemaInt(enum: [1, "2"]);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $wrongType,
        "The enum can only have integer values, but has a string"
    );

    $repeated = function () : void {
        new SchemaString(enum: ["a", "a"]);
    };

    SmokeTest::expectException(InvalidValueException::class, $repeated, 'The enum has "a" in it twice');

    $defaultNotInEnum = function () : void {
        new SchemaString(enum: ["a"], default: "b");
    };

    SmokeTest::expectException(InvalidValueException::class, $defaultNotInEnum, "The schema's default (\"b\") isn't");

    $exampleNotInEnum = function () : void {
        new SchemaNumber(enum: [1.5], example: 2);
    };

    SmokeTest::expectException(InvalidValueException::class, $exampleNotInEnum, "The schema's example (2) isn't");
};

$smoke->run("Schema enums", $testSchemaEnums);

$testCommonSchemaOptions = function () : void {
    $docs = new ExternalDocumentation("https://example.com/docs");
    $extensions = new ExtensionMap(new Extension("x-internal", true));

    $string = new SchemaString(
        description: "A name.",
        example: "Bob",
        title: "Name",
        deprecated: true,
        externalDocs: $docs,
        xml: new Xml(name: "fullName"),
        extensions: $extensions,
    );

    $expected = <<<'JSON'
        {
            "type": "string",
            "title": "Name",
            "description": "A name.",
            "example": "Bob",
            "deprecated": true,
            "externalDocs": {"url": "https://example.com/docs"},
            "xml": {"name": "fullName"},
            "x-internal": true
        }
        JSON;

    SmokeTest::assertJson($expected, $string);

    $bool = new SchemaBool(example: false, title: "Active", deprecated: true, externalDocs: $docs);

    $expected = '{"type":"boolean","title":"Active","example":false,"deprecated":true,'
        . '"externalDocs":{"url":"https://example.com/docs"}}';

    SmokeTest::assertJson($expected, $bool);

    $int = new SchemaInt(example: 3, title: "Count", extensions: $extensions);
    SmokeTest::assertJson('{"type":"integer","title":"Count","example":3,"x-internal":true}', $int);

    $number = new SchemaNumber(example: 1.5, deprecated: true, xml: new Xml(attribute: true));
    $expected = '{"type":"number","example":1.5,"deprecated":true,"xml":{"attribute":true}}';
    SmokeTest::assertJson($expected, $number);
};

$smoke->run("Common schema options", $testCommonSchemaOptions);

$testArrayAndObjectValues = function () : void {
    $array = new SchemaArray(new SchemaString(), default: ["a"], example: ["a", "b"], title: "Tags");
    $expected = '{"type":"array","title":"Tags","items":{"type":"string"},"default":["a"],"example":["a","b"]}';
    SmokeTest::assertJson($expected, $array);

    $object = new SchemaObject(default: [], example: ['name' => "Bob"], title: "User");
    SmokeTest::assertJson('{"type":"object","title":"User","default":{},"example":{"name":"Bob"}}', $object);

    $arrayWithKeys = function () : void {
        new SchemaArray(new SchemaString(), default: ['a' => "b"]);
    };

    SmokeTest::expectException(InvalidValueException::class, $arrayWithKeys, "default must be a list");

    $objectAsList = function () : void {
        new SchemaObject(example: ["a", "b"]);
    };

    SmokeTest::expectException(InvalidValueException::class, $objectAsList, "example must be an object");
};

$smoke->run("Array and object defaults and examples", $testArrayAndObjectValues);

$testAdditionalProperties = function () : void {
    $map = new SchemaObject(additionalProperties: new SchemaInt());
    SmokeTest::assertJson('{"type":"object","additionalProperties":{"type":"integer"}}', $map);

    $onlyA = new PropertyMap(new Property("a", new SchemaInt()));
    $closed = new SchemaObject(properties: $onlyA, additionalProperties: false);
    $expected = '{"type":"object","properties":{"a":{"type":"integer"}},"additionalProperties":false}';
    SmokeTest::assertJson($expected, $closed);

    // True is OpenAPI's default, so it isn't output.
    SmokeTest::assertJson('{"type":"object"}', new SchemaObject(additionalProperties: true));

    $tooManyRequired = function () use ($onlyA) : void {
        new SchemaObject(properties: $onlyA, minProperties: 2, additionalProperties: false);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $tooManyRequired,
        "minProperties (2) can't be more than the number of properties (1) when additionalProperties is false"
    );
};

$smoke->run("SchemaObject additionalProperties", $testAdditionalProperties);

$testObjectDiscriminator = function () : void {
    $pet = new SchemaObject(
        properties: new PropertyMap(new Property("petType", new SchemaString(), required: true)),
        discriminator: new Discriminator("petType"),
    );

    $expected = '{"type":"object","properties":{"petType":{"type":"string"}},"required":["petType"],'
        . '"discriminator":{"propertyName":"petType"}}';

    SmokeTest::assertJson($expected, $pet);

    $optionalProperty = function () : void {
        new SchemaObject(
            properties: new PropertyMap(new Property("petType", new SchemaString())),
            discriminator: new Discriminator("petType"),
        );
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $optionalProperty,
        'The discriminator\'s property, "petType", must be one of the object\'s properties, and be required.'
    );
};

$smoke->run("SchemaObject discriminator", $testObjectDiscriminator);

$testPropertyAccess = function () : void {
    $id = new Property("id", new SchemaString(), required: true, access: Access::READ_ONLY);
    assert($id->getAccess() === Access::READ_ONLY);
    SmokeTest::assertJson('{"type":"string","readOnly":true}', $id);

    $password = new Property("password", new SchemaString(), access: Access::WRITE_ONLY);
    SmokeTest::assertJson('{"type":"string","writeOnly":true}', $password);

    // OpenAPI ignores anything beside a reference, so it is wrapped in an allOf.
    $reference = new Property("owner", SchemaReference::createToComponent("User"), access: Access::READ_ONLY);
    SmokeTest::assertJson('{"allOf":[{"$ref":"#/components/schemas/User"}],"readOnly":true}', $reference);

    $plain = new Property("name", new SchemaString());
    assert($plain->getAccess() === Access::READ_WRITE);
    SmokeTest::assertJson('{"type":"string"}', $plain);

    $object = new SchemaObject(properties: new PropertyMap($id, $password));

    $expected = '{"type":"object","properties":{"id":{"type":"string","readOnly":true},'
        . '"password":{"type":"string","writeOnly":true}},"required":["id"]}';

    SmokeTest::assertJson($expected, $object);
};

$smoke->run("Property access", $testPropertyAccess);

$testSchemaAnyValue = function () : void {
    SmokeTest::assertJson('{}', new SchemaAnyValue());

    $full = new SchemaAnyValue(
        description: "Anything.",
        default: new \stdClass(),
        example: ['a' => 1],
        title: "Metadata",
        deprecated: true,
        externalDocs: new ExternalDocumentation("https://example.com"),
    );

    $expected = '{"title":"Metadata","description":"Anything.","default":{},"example":{"a":1},"deprecated":true,'
        . '"externalDocs":{"url":"https://example.com"}}';

    SmokeTest::assertJson($expected, $full);
};

$smoke->run("SchemaAnyValue", $testSchemaAnyValue);

$testXml = function () : void {
    $xml = new Xml("book", "https://example.com/schema", "ex", false, true);
    $expected = '{"name":"book","namespace":"https://example.com/schema","prefix":"ex","wrapped":true}';
    SmokeTest::assertJson($expected, $xml);
    assert($xml->isWrapped() && !$xml->isAttribute());
    SmokeTest::assertJson('{"namespace":"urn:example:books"}', new Xml(namespace: "urn:example:books"));

    $createEmpty = function () : void {
        new Xml();
    };

    $createWithEmptyName = function () : void {
        new Xml(name: "");
    };

    $createWithRelativeNamespace = function () : void {
        new Xml(namespace: "schema");
    };

    $createPrefixWithoutNamespace = function () : void {
        new Xml(prefix: "ex");
    };

    $wrapString = function () : void {
        new SchemaString(xml: new Xml(wrapped: true));
    };

    $objectAsAttribute = function () : void {
        new SchemaObject(xml: new Xml(attribute: true));
    };

    $arrayAsAttribute = function () : void {
        new SchemaArray(new SchemaInt(), xml: new Xml(attribute: true));
    };

    $badCases = [
        "An Xml needs at least one option" => $createEmpty,
        "The XML name can't be an empty string" => $createWithEmptyName,
        "must be an absolute URI" => $createWithRelativeNamespace,
        "needs a namespace" => $createPrefixWithoutNamespace,
        "can only be used for an array's schema" => $wrapString,
        "can't be used for an object" => $objectAsAttribute,
        "can't be used for an array" => $arrayAsAttribute,
    ];

    foreach ($badCases as $message => $createBadXml)
    {
        SmokeTest::expectException(InvalidValueException::class, $createBadXml, $message);
    }

    $wrappedArray = new SchemaArray(new SchemaInt(), xml: new Xml(name: "ids", wrapped: true));
    $expected = '{"type":"array","items":{"type":"integer"},"xml":{"name":"ids","wrapped":true}}';
    SmokeTest::assertJson($expected, $wrappedArray);
};

$smoke->run("Xml", $testXml);
