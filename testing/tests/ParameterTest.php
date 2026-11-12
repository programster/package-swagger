<?php

/*
 * Smoke tests for Parameter, ParameterCollection, NamedParameter and ParameterMap. These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\Reference;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Style;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testParameterFromSchema = function () : void {
    $parameter = Parameter::createFromSchema("id", ParameterLocation::PATH, true, new SchemaString());
    SmokeTest::assertJson('{"name":"id","in":"path","required":true,"schema":{"type":"string"}}', $parameter);

    $fullParameter = Parameter::createFromSchema(
        name: "search",
        in: ParameterLocation::QUERY,
        required: false,
        schema: new SchemaString(),
        description: "What to search for.",
        deprecated: false,
        allowEmptyValue: true,
        style: Style::FORM,
        explode: true,
        allowReserved: false,
        example: "bob",
    );

    $expected = <<<'JSON'
        {
            "name": "search",
            "in": "query",
            "required": false,
            "description": "What to search for.",
            "deprecated": false,
            "allowEmptyValue": true,
            "schema": {"type": "string"},
            "style": "form",
            "explode": true,
            "allowReserved": false,
            "example": "bob"
        }
        JSON;

    SmokeTest::assertJson($expected, $fullParameter);
    SmokeTest::assertJson($expected, $fullParameter->toArray());

    // A plain (non-object) example used to crash.
    $withValue = Parameter::createFromSchema("id", ParameterLocation::QUERY, true, new SchemaString(), example: 5);
    $expected = '{"name":"id","in":"query","required":true,"schema":{"type":"string"},"example":5}';
    SmokeTest::assertJson($expected, $withValue);

    $withObject = Parameter::createFromSchema(
        "id",
        ParameterLocation::QUERY,
        true,
        new SchemaString(),
        example: new ValueExample("abc")
    );

    $expected = '{"name":"id","in":"query","required":true,"schema":{"type":"string"},"example":"abc"}';
    SmokeTest::assertJson($expected, $withObject);

    $examples = new ExampleMap(new NamedExample("a", new ValueExample("abc")));

    $withMap = Parameter::createFromSchema(
        "id",
        ParameterLocation::QUERY,
        true,
        new SchemaString(),
        example: $examples
    );

    $expected = '{"name":"id","in":"query","required":true,"schema":{"type":"string"},'
        . '"examples":{"a":{"value":"abc"}}}';

    SmokeTest::assertJson($expected, $withMap);

    $inPathAllowingEmpty = function () : void {
        Parameter::createFromSchema("id", ParameterLocation::PATH, true, new SchemaString(), allowEmptyValue: true);
    };

    SmokeTest::expectException(\Exception::class, $inPathAllowingEmpty, "allowEmptyValue can only be set to true");
};

$smoke->run("Parameter::createFromSchema()", $testParameterFromSchema);

$testParameterStyles = function () : void {
    $allowedLocations = [
        [Style::MATRIX, ParameterLocation::PATH],
        [Style::LABEL, ParameterLocation::PATH],
        [Style::FORM, ParameterLocation::QUERY],
        [Style::FORM, ParameterLocation::COOKIE],
        [Style::SIMPLE, ParameterLocation::PATH],
        [Style::SIMPLE, ParameterLocation::HEADER],
        [Style::SPACE_DELIMITED, ParameterLocation::QUERY],
        [Style::PIPE_DELIMITED, ParameterLocation::QUERY],
        [Style::DEEP_OBJECT, ParameterLocation::QUERY],
    ];

    foreach ($allowedLocations as [$style, $location])
    {
        $parameter = Parameter::createFromSchema("a", $location, true, new SchemaString(), style: $style);
        assert($parameter->toArray()['style'] === $style, "{$style->value} should be allowed in {$location->value}");

        // A string style used to crash, as the constructor only takes the enum.
        $fromString = Parameter::createFromSchema("a", $location, true, new SchemaString(), style: $style->value);
        assert($fromString->toArray()['style'] === $style);
    }

    $matrixInQuery = function () : void {
        Parameter::createFromSchema("a", ParameterLocation::QUERY, true, new SchemaString(), style: Style::MATRIX);
    };

    $expectedMessage = "The matrix style is only applicable to parameters in the 'path'";
    SmokeTest::expectException(\Exception::class, $matrixInQuery, $expectedMessage);

    $formInPath = function () : void {
        Parameter::createFromSchema("a", ParameterLocation::PATH, true, new SchemaString(), style: "form");
    };

    SmokeTest::expectException(\Exception::class, $formInPath, "in the 'query' or 'cookie'");

    $deepObjectInHeader = function () : void {
        $style = Style::DEEP_OBJECT;
        Parameter::createFromSchema("a", ParameterLocation::HEADER, true, new SchemaString(), style: $style);
    };

    SmokeTest::expectException(\Exception::class, $deepObjectInHeader, "The deepObject style");

    $unknownStyle = function () : void {
        Parameter::createFromSchema("a", ParameterLocation::QUERY, true, new SchemaString(), style: "sideways");
    };

    SmokeTest::expectException(\ValueError::class, $unknownStyle, "sideways");
};

$smoke->run("Parameter styles", $testParameterStyles);

$testParameterFromContentMap = function () : void {
    $content = new ContentMap(MimeType::JSON, new MediaType(new SchemaString()));
    $in = ParameterLocation::QUERY;
    $parameter = Parameter::createFromContentMap("filter", $in, false, $content, "A filter.", true, true);

    $expected = <<<'JSON'
        {
            "name": "filter",
            "in": "query",
            "required": false,
            "description": "A filter.",
            "deprecated": true,
            "allowEmptyValue": true,
            "content": {"application/json": {"schema": {"type": "string"}}}
        }
        JSON;

    SmokeTest::assertJson($expected, $parameter);

    $inHeaderAllowingEmpty = function () use ($content) : void {
        Parameter::createFromContentMap("a", ParameterLocation::HEADER, true, $content, allowEmptyValue: true);
    };

    SmokeTest::expectException(\Exception::class, $inHeaderAllowingEmpty, "allowEmptyValue");
};

$smoke->run("Parameter::createFromContentMap()", $testParameterFromContentMap);

$testParameterCollection = function () : void {
    $collection = new ParameterCollection(new Reference("#/a"));
    $collection->append(Parameter::createFromSchema("b", ParameterLocation::QUERY, false, new SchemaString()));
    $collection[] = new Reference("#/c");
    $expected = '[{"$ref":"#/a"},{"name":"b","in":"query","required":false,"schema":{"type":"string"}},{"$ref":"#/c"}]';
    SmokeTest::assertJson($expected, $collection);

    $appendString = function () use ($collection) : void {
        $collection->append("not a parameter");
    };

    SmokeTest::expectException(\Exception::class, $appendString, "Cannot append non Parameter");

    $setString = function () use ($collection) : void {
        $collection[0] = "not a parameter";
    };

    SmokeTest::expectException(\Exception::class, $setString, "Cannot add a non Parameter value");
};

$smoke->run("ParameterCollection", $testParameterCollection);

$testParameterMap = function () : void {
    $reference = new Reference("#/a");
    $named = new NamedParameter("users_id", $reference);
    assert($named->getName() === "users_id");
    assert($named->getParameter() === $reference);

    $map = new ParameterMap($named, new NamedParameter("groups_id", new Reference("#/b")));
    SmokeTest::assertJson('{"users_id":{"$ref":"#/a"},"groups_id":{"$ref":"#/b"}}', $map);

    $createWithDuplicateNames = function () : void {
        new ParameterMap(new NamedParameter("a", new Reference("#/a")), new NamedParameter("a", new Reference("#/b")));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "Parameter name clash: a");
};

$smoke->run("ParameterMap and NamedParameter", $testParameterMap);
