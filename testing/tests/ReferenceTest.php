<?php

/*
 * Smoke tests for the references (SchemaReference, ParameterReference, etc.). These are run by testing/main.php,
 * which provides $smoke.
 */

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\References\CallbackReference;
use Programster\Swagger\References\ExampleReference;
use Programster\Swagger\References\HeaderReference;
use Programster\Swagger\References\LinkReference;
use Programster\Swagger\References\ParameterReference;
use Programster\Swagger\References\PathItemReference;
use Programster\Swagger\References\RequestBodyReference;
use Programster\Swagger\References\ResponseReference;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testReferences = function () : void {
    // Each kind of reference points at its own part of the components.
    $sections = [
        SchemaReference::class => "schemas",
        ParameterReference::class => "parameters",
        ResponseReference::class => "responses",
        RequestBodyReference::class => "requestBodies",
        LinkReference::class => "links",
        CallbackReference::class => "callbacks",
        HeaderReference::class => "headers",
        ExampleReference::class => "examples",
    ];

    foreach ($sections as $referenceClass => $section)
    {
        $reference = $referenceClass::createToComponent("Thing");
        assert($reference->getReference() === "#/components/{$section}/Thing", $referenceClass);
        SmokeTest::assertJson('{"$ref":"#/components/' . $section . '/Thing"}', $reference);
    }

    SmokeTest::assertJson('{"$ref":"Pet.json"}', SchemaReference::createToDocument("Pet.json"));

    $toPath = SchemaReference::createToDocumentPath("https://example.com/definitions.json", "/Pet");
    SmokeTest::assertJson('{"$ref":"https://example.com/definitions.json#/Pet"}', $toPath);

    $createWithBadName = function () : void {
        SchemaReference::createToComponent("#/components/schemas/Pet");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadName, "can only contain letters, numbers");

    $createWithHash = function () : void {
        SchemaReference::createToDocument("Pet.json#/Pet");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithHash, "Use createToDocumentPath()");

    $createWithBadPointer = function () : void {
        SchemaReference::createToDocumentPath("definitions.json", "Pet");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadPointer, "must start with \"/\"");
};

$smoke->run("References", $testReferences);

$testPathItemReference = function () : void {
    // OpenAPI 3.0 has no path items in the components, so a path item reference can only be to another document.
    assert(!method_exists(PathItemReference::class, "createToComponent"));
    SmokeTest::assertJson('{"$ref":"paths/health.json"}', PathItemReference::createToDocument("paths/health.json"));

    $pathInDocument = PathItemReference::createToDocumentPath("shared.json", "/paths/~1health");
    SmokeTest::assertJson('{"$ref":"shared.json#/paths/~1health"}', $pathInDocument);
};

$smoke->run("PathItemReference", $testPathItemReference);
