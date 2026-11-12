<?php

/*
 * Smoke tests for the schemas (SchemaString, SchemaInt, etc.), SchemaMap and NamedSchema. These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Format;
use Programster\Swagger\Reference;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\SchemaArray;
use Programster\Swagger\Schemas\SchemaBool;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaNumber;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testSchemaString = function () : void {
    SmokeTest::assertJson('{"type":"string"}', new SchemaString());

    $schema = new SchemaString(
        description: "An email address.",
        default: "a@example.com",
        minLength: 3,
        maxLength: 255,
        pattern: "^.+@.+$",
        format: Format::EMAIL,
        nullable: true,
    );

    $expected = <<<'JSON'
        {
            "type": "string",
            "description": "An email address.",
            "default": "a@example.com",
            "minLength": 3,
            "maxLength": 255,
            "pattern": "^.+@.+$",
            "format": "email",
            "nullable": true
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    // An empty string is still a value, so it is kept, unlike null.
    SmokeTest::assertJson('{"type":"string","default":""}', new SchemaString(default: ""));
};

$smoke->run("SchemaString", $testSchemaString);

$testSchemaInt = function () : void {
    SmokeTest::assertJson('{"type":"integer"}', new SchemaInt());

    $schema = new SchemaInt(
        description: "A count.",
        minimum: 1,
        maximum: 10,
        multipleOf: 2,
        exclusiveMaximum: 9,
        exclusiveMinimum: 0,
        default: 4,
        nullable: true,
    );

    $expected = <<<'JSON'
        {
            "type": "integer",
            "description": "A count.",
            "minimum": 1,
            "maximum": 10,
            "multipleOf": 2,
            "exclusiveMinimum": 0,
            "exclusiveMaximum": 9,
            "default": 4,
            "nullable": true
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    // Zero is still a value, so it is kept, unlike null.
    SmokeTest::assertJson('{"type":"integer","minimum":0,"default":0}', new SchemaInt(minimum: 0, default: 0));
};

$smoke->run("SchemaInt", $testSchemaInt);

$testSchemaNumber = function () : void {
    SmokeTest::assertJson('{"type":"number"}', new SchemaNumber());

    $schema = new SchemaNumber(
        description: "A price.",
        minimum: 1.5,
        maximum: 10,
        multipleOf: 0.5,
        exclusiveMaximum: 9,
        exclusiveMinimum: 0,
        default: 4.5,
        nullable: true,
    );

    $expected = <<<'JSON'
        {
            "type": "number",
            "description": "A price.",
            "minimum": 1.5,
            "maximum": 10,
            "multipleOf": 0.5,
            "exclusiveMinimum": 0,
            "exclusiveMaximum": 9,
            "default": 4.5,
            "nullable": true
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);
};

$smoke->run("SchemaNumber", $testSchemaNumber);

$testSchemaBool = function () : void {
    SmokeTest::assertJson('{"type":"boolean"}', new SchemaBool());
    SmokeTest::assertJson('{"type":"boolean","description":"Active.","default":true}', new SchemaBool("Active.", true));

    // False is still a value, so it is kept, unlike null.
    SmokeTest::assertJson('{"type":"boolean","default":false}', new SchemaBool(default: false));
};

$smoke->run("SchemaBool", $testSchemaBool);

$testSchemaArray = function () : void {
    SmokeTest::assertJson('{"type":"array"}', new SchemaArray());

    $schema = new SchemaArray(
        description: "Some names.",
        minItems: 1,
        maxItems: 5,
        items: new SchemaString(),
        uniqueItems: true,
        minContains: 0,
        maxContains: 3,
    );

    $expected = <<<'JSON'
        {
            "type": "array",
            "description": "Some names.",
            "minItems": 1,
            "maxItems": 5,
            "uniqueItems": true,
            "minContains": 0,
            "maxContains": 3,
            "items": {"type": "string"}
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    $createWithNegativeMinContains = function () : void {
        new SchemaArray(minContains: -1);
    };

    SmokeTest::expectException(\Exception::class, $createWithNegativeMinContains, "minContains cannot be less than 0");

    $createWithNegativeMaxContains = function () : void {
        new SchemaArray(maxContains: -1);
    };

    SmokeTest::expectException(\Exception::class, $createWithNegativeMaxContains, "maxContains cannot be less than 0");
};

$smoke->run("SchemaArray", $testSchemaArray);

$testSchemaObject = function () : void {
    SmokeTest::assertJson('{"type":"object"}', new SchemaObject());

    $schema = new SchemaObject(
        description: "A user.",
        properties: new SchemaMap(new NamedSchema("name", new SchemaString())),
        requiredProperties: ["name"],
        minProperties: 1,
        maxProperties: 2,
    );

    $expected = <<<'JSON'
        {
            "type": "object",
            "description": "A user.",
            "properties": {"name": {"type": "string"}},
            "required": ["name"],
            "minProperties": 1,
            "maxProperties": 2
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);
};

$smoke->run("SchemaObject", $testSchemaObject);

$testSchemaMap = function () : void {
    $namedSchema = new NamedSchema("id", new SchemaString(format: Format::UUID));
    assert($namedSchema->getName() === "id");
    SmokeTest::assertJson('{"type":"string","format":"uuid"}', $namedSchema->getSchema());

    $map = new SchemaMap(
        $namedSchema,
        new NamedSchema("active", new SchemaBool()),
        new NamedSchema("group", Reference::createReferenceObject("Group")),
    );

    $expected = '{"id":{"type":"string","format":"uuid"},"active":{"type":"boolean"},"group":'
        . '{"$ref":"#/components/schemas/Group"}}';

    SmokeTest::assertJson($expected, $map);

    $createWithDuplicateNames = function () : void {
        new SchemaMap(new NamedSchema("id", new SchemaString()), new NamedSchema("id", new SchemaInt()));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "id already taken");
};

$smoke->run("SchemaMap and NamedSchema", $testSchemaMap);
