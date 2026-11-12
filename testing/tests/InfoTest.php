<?php

/*
 * Smoke tests for Info and what goes in it (Contact and License), and for Tag, TagCollection and
 * ExternalDocumentation. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Contact;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\Tag;
use Programster\Swagger\TagCollection;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testInfo = function () : void {
    SmokeTest::assertJson('{"title":"My API","version":"1.0"}', new Info("My API", "1.0"));

    $info = new Info(
        title: "My API",
        version: "1.0",
        description: "An API.",
        termsOfService: "https://example.com/terms",
        contact: new Contact(name: "Support"),
        license: new License("MIT", null),
    );

    $expected = <<<'JSON'
        {
            "title": "My API",
            "version": "1.0",
            "description": "An API.",
            "termsOfService": "https://example.com/terms",
            "contact": {"name": "Support"},
            "license": {"name": "MIT"}
        }
        JSON;

    SmokeTest::assertJson($expected, $info);
};

$smoke->run("Info", $testInfo);

$testContact = function () : void {
    $contact = new Contact("Support", "https://example.com", "support@example.com");
    SmokeTest::assertJson('{"name":"Support","url":"https://example.com","email":"support@example.com"}', $contact);
    SmokeTest::assertJson('{"email":"support@example.com"}', new Contact(email: "support@example.com"));

    $createEmptyContact = function () : void {
        new Contact();
    };

    SmokeTest::expectException(\Exception::class, $createEmptyContact, "at least one of name, url, or email");
};

$smoke->run("Contact", $testContact);

$testLicense = function () : void {
    SmokeTest::assertJson('{"name":"MIT"}', new License("MIT", null));

    $license = new License("MIT", "https://opensource.org/licenses/MIT");
    SmokeTest::assertJson('{"name":"MIT","url":"https://opensource.org/licenses/MIT"}', $license);
};

$smoke->run("License", $testLicense);

$testExternalDocumentation = function () : void {
    SmokeTest::assertJson('{"url":"https://docs.example.com"}', new ExternalDocumentation("https://docs.example.com"));

    $docs = new ExternalDocumentation("https://docs.example.com", "The docs.");
    SmokeTest::assertJson('{"url":"https://docs.example.com","description":"The docs."}', $docs);
};

$smoke->run("ExternalDocumentation", $testExternalDocumentation);

$testTags = function () : void {
    SmokeTest::assertJson('{"name":"users"}', new Tag("users"));

    $tag = new Tag("users", "Managing users.", new ExternalDocumentation("https://docs.example.com/users"));

    $expected = '{"name":"users","description":"Managing users.",'
        . '"externalDocs":{"url":"https://docs.example.com/users"}}';

    SmokeTest::assertJson($expected, $tag);

    $tags = new TagCollection(new Tag("users"), new Tag("groups", "Managing groups."));
    SmokeTest::assertJson('[{"name":"users"},{"name":"groups","description":"Managing groups."}]', $tags);
};

$smoke->run("Tag and TagCollection", $testTags);
