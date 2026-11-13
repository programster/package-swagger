<?php

/*
 * Smoke tests for specification extensions (Extension and ExtensionMap), and that the objects which take them output
 * them. These are run by testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Callback;
use Programster\Swagger\Components;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Contact;
use Programster\Swagger\Encoding;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Header;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\Link;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\RequestBody;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Server;
use Programster\Swagger\ServerVariable;
use Programster\Swagger\Tag;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testExtension = function () : void {
    $extension = new Extension("x-logo", ['url' => "https://example.com/logo.png"]);
    assert($extension->getName() === "x-logo");
    assert($extension->getValue() === ['url' => "https://example.com/logo.png"]);

    $map = new ExtensionMap($extension, new Extension("x-count", 3), new Extension("x-empty", new \stdClass()));
    SmokeTest::assertJson('{"x-logo":{"url":"https://example.com/logo.png"},"x-count":3,"x-empty":{}}', $map);

    $badNames = [
        "logo" => 'must start with "x-"',
        "x-" => "have something after it",
        "X-logo" => 'must start with "x-"',
        "x-oai-anything" => "are reserved for OpenAPI itself",
        "x-OAS-anything" => "are reserved for OpenAPI itself",
    ];

    foreach ($badNames as $name => $message)
    {
        $createWithName = function () use ($name) : void {
            new Extension($name, true);
        };

        SmokeTest::expectException(InvalidValueException::class, $createWithName, $message);
    }

    $createWithDuplicateNames = function () use ($extension) : void {
        new ExtensionMap($extension, new Extension("x-logo", "other"));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'an extension named "x-logo"');
};

$smoke->run("Extension and ExtensionMap", $testExtension);

$testObjectsOutputExtensions = function () : void {
    $extensions = new ExtensionMap(new Extension("x-a", 1));
    $ok = new ResponseCollection(new MappedResponse(ResponseCode::OK, new Response("OK.")));
    $schema = new SchemaString();
    $flow = new ClientCredentialsFlow("https://example.com/token", new ScopeMap(), extensions: $extensions);

    // Each of these should have the extension as its last field.
    $objects = [
        new Info("API", "1.0", extensions: $extensions),
        new Contact("Support", extensions: $extensions),
        new License("MIT", extensions: $extensions),
        new Server("https://example.com", extensions: $extensions),
        new ServerVariable("port", "443", extensions: $extensions),
        new Tag("users", extensions: $extensions),
        new ExternalDocumentation("https://example.com", extensions: $extensions),
        new Components(extensions: $extensions),
        new PathItem(extensions: $extensions),
        new Operation(Method::GET, $ok, extensions: $extensions),
        Parameter::createFromSchema("q", ParameterLocation::QUERY, $schema, extensions: $extensions),
        Parameter::createFromContentMap(
            "q",
            ParameterLocation::QUERY,
            new ContentMap(new MediaType(MimeType::JSON)),
            extensions: $extensions
        ),
        new RequestBody(new ContentMap(new MediaType(MimeType::JSON)), extensions: $extensions),
        new MediaType(MimeType::JSON, extensions: $extensions),
        new Encoding("photo", extensions: $extensions),
        new Response("OK.", extensions: $extensions),
        Header::createFromSchema($schema, extensions: $extensions),
        Header::createFromContentMap(new ContentMap(new MediaType(MimeType::JSON)), extensions: $extensions),
        Link::createFromOperationId("getUser", extensions: $extensions),
        Link::createFromOperationRef("#/paths/~1users/get", extensions: $extensions),
        new Callback('{$request.body#/url}', new PathItem(), $extensions),
        new NamedExample("one", new ValueExample(1), extensions: $extensions),
        $flow,
        new OauthFlows(clientCredentials: $flow, extensions: $extensions),
    ];

    foreach ($objects as $object)
    {
        $fields = get_object_vars($object->jsonSerialize());
        $className = get_class($object);
        assert(array_key_last($fields) === "x-a", "{$className} doesn't output its extensions.");
        assert($fields["x-a"] === 1);
    }

    $document = new OpenApi(new Info("API", "1.0"), new PathCollection(), extensions: $extensions);
    $fields = get_object_vars($document->jsonSerialize());
    assert(array_key_last($fields) === "x-a");

    // Components with only extensions aren't empty, so are output.
    assert(!(new Components(extensions: $extensions))->isEmpty());
};

$smoke->run("Objects output their extensions", $testObjectsOutputExtensions);
