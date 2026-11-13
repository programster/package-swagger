<?php

/*
 * Smoke tests for combining schemas: SchemaCollection, SchemaAllOf, SchemaOneOf, SchemaAnyOf, SchemaNot, and
 * Discriminator with its mappings. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Schemas\Discriminator;
use Programster\Swagger\Schemas\DiscriminatorMapping;
use Programster\Swagger\Schemas\DiscriminatorMappingMap;
use Programster\Swagger\Schemas\SchemaAllOf;
use Programster\Swagger\Schemas\SchemaAnyOf;
use Programster\Swagger\Schemas\SchemaCollection;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaNot;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaOneOf;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testSchemaAllOf = function () : void {
    $user = SchemaReference::createToComponent("User");
    $allOf = new SchemaAllOf(new SchemaCollection($user, new SchemaObject()), "An admin.");
    $expected = '{"description":"An admin.","allOf":[{"$ref":"#/components/schemas/User"},{"type":"object"}]}';
    SmokeTest::assertJson($expected, $allOf);
    assert(count($allOf->getSchemas()) === 2);

    $full = new SchemaAllOf(
        schemas: new SchemaCollection($user),
        example: ['name' => "Bob"],
        title: "Owner",
        deprecated: true,
        externalDocs: new ExternalDocumentation("https://example.com"),
        extensions: new ExtensionMap(new Extension("x-a", 1)),
    );

    $expected = '{"title":"Owner","allOf":[{"$ref":"#/components/schemas/User"}],"example":{"name":"Bob"},'
        . '"deprecated":true,"externalDocs":{"url":"https://example.com"},"x-a":1}';

    SmokeTest::assertJson($expected, $full);

    $createEmpty = function () : void {
        new SchemaAllOf(new SchemaCollection());
    };

    SmokeTest::expectException(InvalidValueException::class, $createEmpty, "needs at least one schema");
};

$smoke->run("SchemaAllOf and SchemaCollection", $testSchemaAllOf);

$testSchemaOneOfAndAnyOf = function () : void {
    $schemas = new SchemaCollection(new SchemaInt(), new SchemaString());
    $oneOf = new SchemaOneOf($schemas, "An ID.");
    SmokeTest::assertJson('{"description":"An ID.","oneOf":[{"type":"integer"},{"type":"string"}]}', $oneOf);
    SmokeTest::assertJson('{"anyOf":[{"type":"integer"},{"type":"string"}]}', new SchemaAnyOf($schemas));

    // The schemas were copied, so changing the collection doesn't change the schema.
    $schemas->add(new SchemaObject());
    assert(count($oneOf->getSchemas()) === 2);

    $oneOfOne = function () : void {
        new SchemaOneOf(new SchemaCollection(new SchemaInt()));
    };

    SmokeTest::expectException(InvalidValueException::class, $oneOfOne, "A SchemaOneOf needs at least two schemas");

    $anyOfOne = function () : void {
        new SchemaAnyOf(new SchemaCollection(new SchemaInt()));
    };

    SmokeTest::expectException(InvalidValueException::class, $anyOfOne, "A SchemaAnyOf needs at least two schemas");
};

$smoke->run("SchemaOneOf and SchemaAnyOf", $testSchemaOneOfAndAnyOf);

$testSchemaNot = function () : void {
    $notString = new SchemaNot(new SchemaString(), "Anything but a string.", "Not a string");
    $expected = '{"title":"Not a string","description":"Anything but a string.","not":{"type":"string"}}';
    SmokeTest::assertJson($expected, $notString);
};

$smoke->run("SchemaNot", $testSchemaNot);

$testDiscriminator = function () : void {
    $cat = SchemaReference::createToComponent("Cat");
    $dog = SchemaReference::createToComponent("Dog");
    $catMapping = new DiscriminatorMapping("cat", $cat);
    assert($catMapping->getValue() === "cat" && $catMapping->getSchema() === $cat);
    $mapping = new DiscriminatorMappingMap($catMapping, new DiscriminatorMapping("dog", $dog));
    $discriminator = new Discriminator("petType", $mapping);
    assert($discriminator->getPropertyName() === "petType");
    assert($discriminator->getMapping()->getReferences() === ["#/components/schemas/Cat", "#/components/schemas/Dog"]);

    $pet = new SchemaOneOf(new SchemaCollection($cat, $dog), discriminator: $discriminator);

    $expected = <<<'JSON'
        {
            "oneOf": [{"$ref": "#/components/schemas/Cat"}, {"$ref": "#/components/schemas/Dog"}],
            "discriminator": {
                "propertyName": "petType",
                "mapping": {"cat": "#/components/schemas/Cat", "dog": "#/components/schemas/Dog"}
            }
        }
        JSON;

    SmokeTest::assertJson($expected, $pet);

    $anyPet = new SchemaAnyOf(new SchemaCollection($cat, $dog), discriminator: new Discriminator("petType"));

    $expected = '{"anyOf":[{"$ref":"#/components/schemas/Cat"},{"$ref":"#/components/schemas/Dog"}],'
        . '"discriminator":{"propertyName":"petType"}}';

    SmokeTest::assertJson($expected, $anyPet);

    $inlineSchema = function () use ($cat) : void {
        new SchemaOneOf(new SchemaCollection($cat, new SchemaObject()), discriminator: new Discriminator("petType"));
    };

    SmokeTest::expectException(InvalidValueException::class, $inlineSchema, "every schema must be a reference");

    $mappingToOtherSchema = function () use ($cat, $dog) : void {
        $bird = SchemaReference::createToComponent("Bird");
        $mapping = new DiscriminatorMappingMap(new DiscriminatorMapping("bird", $bird));
        new SchemaAnyOf(new SchemaCollection($cat, $dog), discriminator: new Discriminator("petType", $mapping));
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $mappingToOtherSchema,
        'maps a value to "#/components/schemas/Bird", which isn\'t one of the schemas'
    );

    $mapSameValueTwice = function () use ($cat, $dog) : void {
        new DiscriminatorMappingMap(new DiscriminatorMapping("pet", $cat), new DiscriminatorMapping("pet", $dog));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $mapSameValueTwice, 'a mapping for "pet"');

    $emptyPropertyName = function () : void {
        new Discriminator("");
    };

    SmokeTest::expectException(InvalidValueException::class, $emptyPropertyName, "property name can't be an empty");
};

$smoke->run("Discriminator", $testDiscriminator);
