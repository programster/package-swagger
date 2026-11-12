<?php

/*
 * Smoke tests for Server, ServerCollection, ServerVariable and ServerVariableMap. These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\ServerVariable;
use Programster\Swagger\ServerVariableMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testServer = function () : void {
    SmokeTest::assertJson('{"url":"https://api.example.com"}', new Server("https://api.example.com", null, null));

    $variables = new ServerVariableMap("port", new ServerVariable("8443", "The port.", ["8443", "443"]));
    $variables->add("environment", new ServerVariable("production"));
    $variables->addOrReplace("environment", new ServerVariable("staging"));
    $server = new Server("https://{environment}.example.com:{port}", "The API.", $variables);

    $expected = <<<'JSON'
        {
            "url": "https://{environment}.example.com:{port}",
            "description": "The API.",
            "variables": {
                "port": {"default": "8443", "enum": ["8443", "443"], "description": "The port."},
                "environment": {"default": "staging"}
            }
        }
        JSON;

    SmokeTest::assertJson($expected, $server);

    $addDuplicate = function () use ($variables) : void {
        $variables->add("port", new ServerVariable("80"));
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "port already taken");
};

$smoke->run("Server and ServerVariableMap", $testServer);

$testServerVariable = function () : void {
    $createWithEmptyEnum = function () : void {
        new ServerVariable("a", null, []);
    };

    SmokeTest::expectException(\Exception::class, $createWithEmptyEnum, "Enum cannot be an empty array");

    $createWithNumericEnum = function () : void {
        new ServerVariable("443", null, [443]);
    };

    SmokeTest::expectException(\Exception::class, $createWithNumericEnum, "need to all be strings");
};

$smoke->run("ServerVariable", $testServerVariable);

$testServerCollection = function () : void {
    $collection = new ServerCollection(new Server("https://a.example.com", null, null));
    $collection->append(new Server("https://b.example.com", null, null));
    $collection[] = new Server("https://c.example.com", null, null);
    $expected = '[{"url":"https://a.example.com"},{"url":"https://b.example.com"},{"url":"https://c.example.com"}]';
    SmokeTest::assertJson($expected, $collection);

    $appendString = function () use ($collection) : void {
        $collection->append("https://d.example.com");
    };

    SmokeTest::expectException(\Exception::class, $appendString, "Cannot append non Server");

    $setString = function () use ($collection) : void {
        $collection[0] = "https://d.example.com";
    };

    SmokeTest::expectException(\Exception::class, $setString, "Cannot add a non Server value");
};

$smoke->run("ServerCollection", $testServerCollection);
