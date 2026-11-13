<?php

/*
 * Smoke tests for the schemas (SchemaString, SchemaInt, etc.), properties, SchemaMap and NamedSchema. These are run
 * by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Schemas\IntegerFormat;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\NumberFormat;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaArray;
use Programster\Swagger\Schemas\SchemaBool;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaNumber;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;
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
        format: StringFormat::EMAIL,
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
    SmokeTest::assertJson('{"type":"string","format":"binary"}', new SchemaString(format: StringFormat::BINARY));

    $createWithMinOverMax = function () : void {
        new SchemaString(minLength: 5, maxLength: 4);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithMinOverMax,
        "minLength (5) can't be greater than maxLength (4)"
    );

    $createWithNegativeLength = function () : void {
        new SchemaString(minLength: -1);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithNegativeLength, "minLength can't be negative");
};

$smoke->run("SchemaString", $testSchemaString);

$testSchemaInt = function () : void {
    SmokeTest::assertJson('{"type":"integer"}', new SchemaInt());

    $schema = new SchemaInt(
        description: "A count.",
        minimum: 0,
        maximum: 10,
        exclusiveMinimum: true,
        exclusiveMaximum: true,
        multipleOf: 2,
        default: 4,
        format: IntegerFormat::INT32,
        nullable: true,
    );

    // In OpenAPI 3.0, exclusiveMinimum and exclusiveMaximum are flags on the minimum and maximum, not numbers.
    $expected = <<<'JSON'
        {
            "type": "integer",
            "description": "A count.",
            "format": "int32",
            "minimum": 0,
            "exclusiveMinimum": true,
            "maximum": 10,
            "exclusiveMaximum": true,
            "multipleOf": 2,
            "default": 4,
            "nullable": true
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    $createExclusiveWithoutMinimum = function () : void {
        new SchemaInt(exclusiveMinimum: true);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createExclusiveWithoutMinimum,
        "exclusiveMinimum needs a minimum"
    );

    $createExclusiveWithoutMaximum = function () : void {
        new SchemaInt(exclusiveMaximum: true);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createExclusiveWithoutMaximum,
        "exclusiveMaximum needs a maximum"
    );

    $createWithMinOverMax = function () : void {
        new SchemaInt(minimum: 2, maximum: 1);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithMinOverMax, "minimum (2) can't be greater");

    $createWithZeroMultiple = function () : void {
        new SchemaInt(multipleOf: 0);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithZeroMultiple,
        "multipleOf must be greater than 0"
    );
};

$smoke->run("SchemaInt", $testSchemaInt);

$testSchemaNumber = function () : void {
    SmokeTest::assertJson('{"type":"number"}', new SchemaNumber());

    $schema = new SchemaNumber(
        minimum: 1.5,
        maximum: 10,
        multipleOf: 0.5,
        default: 4.5,
        format: NumberFormat::DOUBLE,
    );

    $expected = '{"type":"number","format":"double","minimum":1.5,"maximum":10,"multipleOf":0.5,"default":4.5}';
    SmokeTest::assertJson($expected, $schema);

    $createWithNegativeMultiple = function () : void {
        new SchemaNumber(multipleOf: -0.5);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithNegativeMultiple,
        "multipleOf must be greater than 0"
    );
};

$smoke->run("SchemaNumber", $testSchemaNumber);

$testSchemaBool = function () : void {
    SmokeTest::assertJson('{"type":"boolean"}', new SchemaBool());
    SmokeTest::assertJson('{"type":"boolean","description":"Active.","default":true}', new SchemaBool("Active.", true));

    // False is still a value, so it is kept, unlike null.
    SmokeTest::assertJson(
        '{"type":"boolean","default":false,"nullable":true}',
        new SchemaBool(default: false, nullable: true)
    );
};

$smoke->run("SchemaBool", $testSchemaBool);

$testSchemaArray = function () : void {
    SmokeTest::assertJson('{"type":"array","items":{"type":"string"}}', new SchemaArray(new SchemaString()));

    $schema = new SchemaArray(
        items: SchemaReference::createToComponent("User"),
        description: "Some users.",
        minItems: 1,
        maxItems: 5,
        uniqueItems: true,
        nullable: true,
    );

    $expected = <<<'JSON'
        {
            "type": "array",
            "description": "Some users.",
            "items": {"$ref": "#/components/schemas/User"},
            "minItems": 1,
            "maxItems": 5,
            "uniqueItems": true,
            "nullable": true
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    $createWithMinOverMax = function () : void {
        new SchemaArray(new SchemaString(), minItems: 3, maxItems: 2);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithMinOverMax,
        "minItems (3) can't be greater than maxItems (2)"
    );
};

$smoke->run("SchemaArray", $testSchemaArray);

$testSchemaObject = function () : void {
    SmokeTest::assertJson('{"type":"object"}', new SchemaObject());

    $properties = new PropertyMap(
        new Property("id", new SchemaString(format: StringFormat::UUID), true),
        new Property("name", new SchemaString(), required: true),
        new Property("age", new SchemaInt()),
    );

    assert($properties->getRequiredNames() === ["id", "name"]);
    $schema = new SchemaObject("A user.", $properties, minProperties: 2, maxProperties: 3);

    $expected = <<<'JSON'
        {
            "type": "object",
            "description": "A user.",
            "properties": {
                "id": {"type": "string", "format": "uuid"},
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["id", "name"],
            "minProperties": 2,
            "maxProperties": 3
        }
        JSON;

    SmokeTest::assertJson($expected, $schema);

    // The schema keeps a copy of the properties, so changing them afterwards doesn't change it.
    $properties->add(new Property("email", new SchemaString()));
    SmokeTest::assertJson($expected, $schema);
    assert(count($schema->getProperties()) === 3);

    $createWithTooFewMax = function () use ($properties) : void {
        new SchemaObject(properties: $properties, maxProperties: 1);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithTooFewMax,
        "can't be fewer than the number of required properties (2)"
    );

    $createWithDuplicateProperties = function () : void {
        new PropertyMap(new Property("id", new SchemaString()), new Property("id", new SchemaInt()));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateProperties, 'a property named "id"');
};

$smoke->run("SchemaObject and PropertyMap", $testSchemaObject);

$testSchemaMap = function () : void {
    $namedSchema = new NamedSchema("User", new SchemaObject());
    assert($namedSchema->getName() === "User");
    SmokeTest::assertJson('{"type":"object"}', $namedSchema->getSchema());
    SmokeTest::assertJson('{"$ref":"#/components/schemas/User"}', $namedSchema->createReference());

    $map = new SchemaMap($namedSchema, new NamedSchema("Active", new SchemaBool()));
    SmokeTest::assertJson('{"User":{"type":"object"},"Active":{"type":"boolean"}}', $map);
    assert($map->hasKey("User") && !$map->hasKey("Group"));

    $createWithDuplicateNames = function () use ($namedSchema) : void {
        new SchemaMap($namedSchema, new NamedSchema("User", new SchemaString()));
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $createWithDuplicateNames,
        'SchemaMap already has a schema named "User"'
    );

    $createWithBadName = function () : void {
        new NamedSchema("User Account", new SchemaObject());
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadName, "can only contain letters, numbers");
};

$smoke->run("SchemaMap and NamedSchema", $testSchemaMap);
