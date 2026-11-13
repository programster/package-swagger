<?php

/*
 * Smoke tests for Server, ServerCollection, ServerVariable and ServerVariableMap. These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\ServerVariable;
use Programster\Swagger\ServerVariableMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testServer = function () : void {
    SmokeTest::assertJson('{"url":"https://api.example.com"}', new Server("https://api.example.com"));
    SmokeTest::assertJson('{"url":"/v1","description":"Relative."}', new Server("/v1", "Relative."));

    $variables = new ServerVariableMap(
        new ServerVariable("port", "8443", "The port.", ["8443", "443"]),
        new ServerVariable("environment", "production"),
    );

    $server = new Server("https://{environment}.example.com:{port}", "The API.", $variables);

    $expected = <<<'JSON'
        {
            "url": "https://{environment}.example.com:{port}",
            "description": "The API.",
            "variables": {
                "port": {"enum": ["8443", "443"], "default": "8443", "description": "The port."},
                "environment": {"default": "production"}
            }
        }
        JSON;

    SmokeTest::assertJson($expected, $server);

    $createWithUndefinedVariable = function () : void {
        new Server("https://{environment}.example.com");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithUndefinedVariable,
        'has a {environment} variable, but no ServerVariable'
    );

    $createWithUnusedVariable = function () use ($variables) : void {
        new Server("https://{environment}.example.com", variables: $variables);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithUnusedVariable,
        'A ServerVariable named "port" was given'
    );

    $createWithDuplicateVariables = function () : void {
        new ServerVariableMap(new ServerVariable("port", "80"), new ServerVariable("port", "443"));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateVariables, 'a variable named "port"');
};

$smoke->run("Server and ServerVariableMap", $testServer);

$testServerVariable = function () : void {
    assert((new ServerVariable("port", "80"))->getName() === "port");

    $createWithEmptyEnum = function () : void {
        new ServerVariable("port", "80", null, []);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithEmptyEnum, "enum can't be empty");

    $createWithNumericEnum = function () : void {
        new ServerVariable("port", "443", null, [443]);
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithNumericEnum, "enum has a int, not a string");

    $createWithDefaultNotInEnum = function () : void {
        new ServerVariable("port", "80", null, ["443"]);
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $createWithDefaultNotInEnum,
        "isn't one of its enum's values"
    );
};

$smoke->run("ServerVariable", $testServerVariable);

$testServerCollection = function () : void {
    $collection = new ServerCollection(new Server("https://a.example.com"), new Server("https://b.example.com"));
    SmokeTest::assertJson('[{"url":"https://a.example.com"},{"url":"https://b.example.com"}]', $collection);
};

$smoke->run("ServerCollection", $testServerCollection);
