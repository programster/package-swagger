<?php

/*
 * Smoke tests for the examples (ValueExample, ExternalValueExample, NamedExample and ExampleMap). These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
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
    assert($example->toArray() === ['externalValue' => "https://example.com/user.json"]);
    SmokeTest::assertJson('{"externalValue":"https://example.com/user.json"}', $example);

    $createWithBadUrl = function () : void {
        new ExternalValueExample("not a url");
    };

    SmokeTest::expectException(\Exception::class, $createWithBadUrl, "externalValue needs to be a valid URL");
};

$smoke->run("ExternalValueExample", $testExternalValueExample);

$testNamedExample = function () : void {
    $value = new ValueExample(1);
    $example = new NamedExample("one", $value, "The number one.", "A longer description of one.");
    assert($example->getName() === "one");
    assert($example->getExample() === $value);
    assert($example->getSummary() === "The number one.");
    assert($example->getDescription() === "A longer description of one.");

    $withoutText = new NamedExample("one", $value);
    assert($withoutText->getSummary() === null && $withoutText->getDescription() === null);
};

$smoke->run("NamedExample", $testNamedExample);

$testExampleMap = function () : void {
    $map = new ExampleMap(
        new NamedExample("user", new ValueExample(['name' => "Bob"]), "A user.", "A user called Bob."),
        new NamedExample("remote", new ExternalValueExample("https://example.com/user.json")),
    );

    $expected = <<<'JSON'
        {
            "user": {"value": {"name": "Bob"}, "description": "A user called Bob.", "summary": "A user."},
            "remote": {"externalValue": "https://example.com/user.json"}
        }
        JSON;

    SmokeTest::assertJson($expected, $map);
    SmokeTest::assertJson($expected, $map->toArray());

    // An empty map used to crash, as its array was never set.
    assert((new ExampleMap())->toArray() === []);

    $map->add(new NamedExample("other", new ValueExample(2)));
    $map->addOrReplace(new NamedExample("user", new ValueExample(3)));
    assert($map->toArray()['user'] === ['value' => 3]);
    assert($map->toArray()['other'] === ['value' => 2]);

    $addDuplicate = function () use ($map) : void {
        $map->add(new NamedExample("other", new ValueExample(4)));
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "other already taken");

    $createWithDuplicateNames = function () : void {
        new ExampleMap(new NamedExample("a", new ValueExample(1)), new NamedExample("a", new ValueExample(2)));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "a already taken");
};

$smoke->run("ExampleMap", $testExampleMap);
