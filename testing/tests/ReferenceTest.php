<?php

/*
 * Smoke tests for Reference. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Reference;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testReference = function () : void {
    $reference = new Reference("#/components/schemas/Pet");
    assert($reference->toArray() === ['$ref' => "#/components/schemas/Pet"]);
    SmokeTest::assertJson('{"$ref":"#/components/schemas/Pet"}', $reference);
    SmokeTest::assertJson('{"$ref":"#/components/schemas/Pet"}', Reference::createReferenceObject("Pet"));
    SmokeTest::assertJson('{"$ref":"Pet.json"}', Reference::createRelativeSchemaDocument("Pet.json"));

    $embedded = Reference::createRelativeDocumentEmbeddedSchema("definitions.json", "Pet");
    SmokeTest::assertJson('{"$ref":"definitions.json#/Pet"}', $embedded);
};

$smoke->run("Reference", $testReference);
