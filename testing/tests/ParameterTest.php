<?php

/*
 * Smoke tests for Parameter, ParameterCollection, NamedParameter and ParameterMap. These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\ContentMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\References\ParameterReference;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Style;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testParameterFromSchema = function () : void {
    $parameter = Parameter::createFromSchema("search", ParameterLocation::QUERY, new SchemaString());
    assert($parameter->getName() === "search");
    assert($parameter->getLocation() === ParameterLocation::QUERY);
    SmokeTest::assertJson('{"name":"search","in":"query","schema":{"type":"string"}}', $parameter);

    // Path parameters are always required, so they don't need to be told.
    $inPath = Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString());
    SmokeTest::assertJson('{"name":"id","in":"path","required":true,"schema":{"type":"string"}}', $inPath);

    $fullParameter = Parameter::createFromSchema(
        name: "search",
        in: ParameterLocation::QUERY,
        schema: new SchemaString(),
        required: true,
        description: "What to search for.",
        deprecated: true,
        allowEmptyValue: true,
        style: Style::FORM,
        explode: false,
        allowReserved: true,
        example: new ValueExample("bob"),
    );

    $expected = <<<'JSON'
        {
            "name": "search",
            "in": "query",
            "description": "What to search for.",
            "required": true,
            "deprecated": true,
            "allowEmptyValue": true,
            "style": "form",
            "explode": false,
            "allowReserved": true,
            "schema": {"type": "string"},
            "example": "bob"
        }
        JSON;

    SmokeTest::assertJson($expected, $fullParameter);

    $examples = new ExampleMap(new NamedExample("a", new ValueExample("abc")));
    $withMap = Parameter::createFromSchema("id", ParameterLocation::QUERY, new SchemaString(), example: $examples);
    $expected = '{"name":"id","in":"query","schema":{"type":"string"},"examples":{"a":{"value":"abc"}}}';
    SmokeTest::assertJson($expected, $withMap);
};

$smoke->run("Parameter::createFromSchema()", $testParameterFromSchema);

$testParameterRules = function () : void {
    $optionalInPath = function () : void {
        Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString(), required: false);
    };

    SmokeTest::expectException(InvalidValueException::class, $optionalInPath, "is in the path, so it must be required");

    $authorizationHeader = function () : void {
        Parameter::createFromSchema("Authorization", ParameterLocation::HEADER, new SchemaString());
    };

    SmokeTest::expectException(InvalidValueException::class, $authorizationHeader, "would be ignored");

    $emptyInHeader = function () : void {
        Parameter::createFromSchema("X-A", ParameterLocation::HEADER, new SchemaString(), allowEmptyValue: true);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $emptyInHeader,
        "allowEmptyValue can only be used for parameters in the query"
    );

    $reservedInCookie = function () : void {
        Parameter::createFromSchema("session", ParameterLocation::COOKIE, new SchemaString(), allowReserved: true);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $reservedInCookie,
        "allowReserved can only be used for parameters in the query"
    );

    $twoMediaTypes = function () : void {
        $content = new ContentMap(new MediaType(MimeType::JSON), new MediaType(MimeType::XML));
        Parameter::createFromContentMap("filter", ParameterLocation::QUERY, $content);
    };

    SmokeTest::expectException(InvalidValueException::class, $twoMediaTypes, "must have exactly one media type");
};

$smoke->run("Parameter rules", $testParameterRules);

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
        $parameter = Parameter::createFromSchema("a", $location, new SchemaString(), style: $style);
        assert($parameter->jsonSerialize()->style === $style->value);
    }

    $matrixInQuery = function () : void {
        Parameter::createFromSchema("a", ParameterLocation::QUERY, new SchemaString(), style: Style::MATRIX);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $matrixInQuery,
        "The matrix style can only be used for parameters in the 'path'"
    );

    $formInPath = function () : void {
        Parameter::createFromSchema("a", ParameterLocation::PATH, new SchemaString(), style: Style::FORM);
    };

    SmokeTest::expectException(InvalidValueException::class, $formInPath, "in the 'query' or 'cookie'");
};

$smoke->run("Parameter styles", $testParameterStyles);

$testParameterFromContentMap = function () : void {
    $content = new ContentMap(new MediaType(MimeType::JSON, new SchemaString()));
    $in = ParameterLocation::QUERY;
    $parameter = Parameter::createFromContentMap("filter", $in, $content, true, "A filter.", true, true);

    $expected = <<<'JSON'
        {
            "name": "filter",
            "in": "query",
            "description": "A filter.",
            "required": true,
            "deprecated": true,
            "allowEmptyValue": true,
            "content": {"application/json": {"schema": {"type": "string"}}}
        }
        JSON;

    SmokeTest::assertJson($expected, $parameter);
};

$smoke->run("Parameter::createFromContentMap()", $testParameterFromContentMap);

$testParameterCollection = function () : void {
    $collection = new ParameterCollection(
        ParameterReference::createToComponent("userId"),
        Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString()),
        Parameter::createFromSchema("id", ParameterLocation::QUERY, new SchemaString()),
    );

    $expected = '[{"$ref":"#/components/parameters/userId"},{"name":"id","in":"path","required":true,'
        . '"schema":{"type":"string"}},{"name":"id","in":"query","schema":{"type":"string"}}]';

    SmokeTest::assertJson($expected, $collection);
    assert($collection->getPathParameterNames() === ["id"]);
    assert($collection->hasReferences());
    assert((new ParameterCollection())->hasReferences() === false);

    $addDuplicate = function () use ($collection) : void {
        $collection->add(Parameter::createFromSchema("id", ParameterLocation::QUERY, new SchemaString()));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $addDuplicate, 'already has a query parameter named "id"');
};

$smoke->run("ParameterCollection", $testParameterCollection);

$testParameterMap = function () : void {
    $parameter = Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString());
    $named = new NamedParameter("userId", $parameter);
    assert($named->getName() === "userId");
    assert($named->getParameter() === $parameter);
    SmokeTest::assertJson('{"$ref":"#/components/parameters/userId"}', $named->createReference());

    $map = new ParameterMap(
        $named,
        new NamedParameter("groupId", ParameterReference::createToDocument("group-id.json"))
    );

    $expected = '{"userId":{"name":"id","in":"path","required":true,"schema":{"type":"string"}},'
        . '"groupId":{"$ref":"group-id.json"}}';

    SmokeTest::assertJson($expected, $map);

    $createWithDuplicateNames = function () use ($named) : void {
        new ParameterMap($named, $named);
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a parameter named "userId"');
};

$smoke->run("ParameterMap and NamedParameter", $testParameterMap);
