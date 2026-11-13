<?php

/*
 * Smoke tests for Info and what goes in it (Contact and License), and for Tag, TagCollection and
 * ExternalDocumentation. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Contact;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
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
        license: new License("MIT"),
    );

    $expected = <<<'JSON'
        {
            "title": "My API",
            "description": "An API.",
            "termsOfService": "https://example.com/terms",
            "contact": {"name": "Support"},
            "license": {"name": "MIT"},
            "version": "1.0"
        }
        JSON;

    SmokeTest::assertJson($expected, $info);

    $createWithoutTitle = function () : void {
        new Info("", "1.0");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithoutTitle, "title can't be an empty string");

    $createWithBadTerms = function () : void {
        new Info("My API", "1.0", termsOfService: "our terms");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadTerms, "must be a URL");
};

$smoke->run("Info", $testInfo);

$testContact = function () : void {
    $contact = new Contact("Support", "https://example.com", "support@example.com");
    SmokeTest::assertJson('{"name":"Support","url":"https://example.com","email":"support@example.com"}', $contact);
    SmokeTest::assertJson('{"email":"support@example.com"}', new Contact(email: "support@example.com"));

    $createEmpty = function () : void {
        new Contact();
    };

    SmokeTest::expectException(InvalidValueException::class, $createEmpty, "at least one of a name, URL or email");

    $createWithBadEmail = function () : void {
        new Contact(email: "support");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadEmail, "must be an email address");

    $createWithBadUrl = function () : void {
        new Contact(url: "example.com");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadUrl, "must be a URL");
};

$smoke->run("Contact", $testContact);

$testLicense = function () : void {
    SmokeTest::assertJson('{"name":"MIT"}', new License("MIT"));

    $license = new License("MIT", "https://opensource.org/licenses/MIT");
    SmokeTest::assertJson('{"name":"MIT","url":"https://opensource.org/licenses/MIT"}', $license);

    $createWithBadUrl = function () : void {
        new License("MIT", "MIT");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadUrl, "must be a URL");
};

$smoke->run("License", $testLicense);

$testExternalDocumentation = function () : void {
    SmokeTest::assertJson('{"url":"https://docs.example.com"}', new ExternalDocumentation("https://docs.example.com"));

    $docs = new ExternalDocumentation("https://docs.example.com", "The docs.");
    SmokeTest::assertJson('{"description":"The docs.","url":"https://docs.example.com"}', $docs);

    $createWithBadUrl = function () : void {
        new ExternalDocumentation("docs");
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadUrl, "must be a URL");
};

$smoke->run("ExternalDocumentation", $testExternalDocumentation);

$testTags = function () : void {
    $tag = new Tag("users", "Managing users.", new ExternalDocumentation("https://docs.example.com/users"));
    assert($tag->getName() === "users");

    $expected = '{"name":"users","description":"Managing users.",'
        . '"externalDocs":{"url":"https://docs.example.com/users"}}';

    SmokeTest::assertJson($expected, $tag);

    $tags = new TagCollection(new Tag("users"), new Tag("groups", "Managing groups."));
    SmokeTest::assertJson('[{"name":"users"},{"name":"groups","description":"Managing groups."}]', $tags);
    assert($tags->getKeys() === ["users", "groups"]);

    $createWithDuplicateNames = function () : void {
        new TagCollection(new Tag("users"), new Tag("users", "Again."));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a tag named "users"');

    // A duplicate is also caught when it is added afterwards.
    $addDuplicate = function () use ($tags) : void {
        $tags->add(new Tag("groups"));
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $addDuplicate,
        'TagCollection already has a tag named "groups"'
    );
};

$smoke->run("Tag and TagCollection", $testTags);
