<?php

/*
 * Smoke tests for the examples (ValueExample, ExternalValueExample, NamedExample and ExampleMap). These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\References\ExampleReference;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testValueExample = function () : void {
    $example = new ValueExample(['message' => "Hello"]);
    assert($example->getValue() === ['message' => "Hello"]);
    SmokeTest::assertJson('{"message":"Hello"}', $example);
    SmokeTest::assertJson('false', new ValueExample(false));
};

$smoke->run("ValueExample", $testValueExample);

$testExternalValueExample = function () : void {
    $example = new ExternalValueExample("https://example.com/user.json");
    assert($example->getUrl() === "https://example.com/user.json");

    $createWithBadUrl = function () : void {
        new ExternalValueExample("not a url");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadUrl, "must be a URL");
};

$smoke->run("ExternalValueExample", $testExternalValueExample);

$testNamedExample = function () : void {
    $value = new ValueExample(1);
    $example = new NamedExample("one", $value, "The number one.", "A longer description of one.");
    assert($example->getName() === "one");
    assert($example->getExample() === $value);
    assert($example->getSummary() === "The number one.");
    assert($example->getDescription() === "A longer description of one.");

    SmokeTest::assertJson(
        '{"summary":"The number one.","description":"A longer description of one.","value":1}',
        $example
    );

    $external = new NamedExample("remote", new ExternalValueExample("https://example.com/user.json"));
    SmokeTest::assertJson('{"externalValue":"https://example.com/user.json"}', $external);

    $createWithoutName = function () : void {
        new NamedExample("", new ValueExample(1));
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithoutName, "name can't be an empty string");
};

$smoke->run("NamedExample", $testNamedExample);

$testExampleMap = function () : void {
    $map = new ExampleMap(
        new NamedExample("user", new ValueExample(['name' => "Bob"]), "A user."),
        new NamedExample("remote", new ExternalValueExample("https://example.com/user.json")),
    );

    $expected = <<<'JSON'
        {
            "user": {"summary": "A user.", "value": {"name": "Bob"}},
            "remote": {"externalValue": "https://example.com/user.json"}
        }
        JSON;

    SmokeTest::assertJson($expected, $map);
    SmokeTest::assertJson('{}', new ExampleMap());

    $createWithDuplicateNames = function () : void {
        new ExampleMap(new NamedExample("a", new ValueExample(1)), new NamedExample("a", new ValueExample(2)));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'an example named "a"');
};

$smoke->run("ExampleMap", $testExampleMap);

$testNamedExampleReference = function () : void {
    $bob = new NamedExample("Bob", new ValueExample("bob"), "A user.");
    $reference = $bob->createReference();
    SmokeTest::assertJson('{"$ref":"#/components/examples/Bob"}', $reference);

    $usesReference = new NamedExample("bob", $reference);
    assert($usesReference->getExample() === $reference);
    SmokeTest::assertJson('{"$ref":"#/components/examples/Bob"}', $usesReference);
    SmokeTest::assertJson('{"bob":{"$ref":"#/components/examples/Bob"}}', new ExampleMap($usesReference));

    $referenceWithSummary = function () use ($reference) : void {
        new NamedExample("bob", $reference, summary: "A user.");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $referenceWithSummary,
        "is a reference, so it can't have a summary, description or extensions"
    );

    $referenceToBadName = function () : void {
        (new NamedExample("Bob Smith", new ValueExample("bob")))->createReference();
    };

    SmokeTest::expectException(InvalidValueException::class, $referenceToBadName, "can only contain letters");
};

$smoke->run("NamedExample with a reference", $testNamedExampleReference);
