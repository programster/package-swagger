<?php

/*
 * Smoke tests for the security schemes (ApiKey, HttpBasicAuth, JwtBearer and OAuth2), NamedSecurityScheme,
 * SecuritySchemeMap, RequiredScheme, SecurityRequirement and SecurityRequirementCollection. These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\ImplicitFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\ApiKeyLocation;
use Programster\Swagger\Security\HttpAuth;
use Programster\Swagger\Security\HttpAuthScheme;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\HttpBearer;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\OAuth2;
use Programster\Swagger\Security\OpenIdConnect;
use Programster\Swagger\Security\RequiredScheme;
use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createOAuth = function () : NamedSecurityScheme {
    $scopes = new ScopeMap(new Scope("read", "Read access."), new Scope("write", "Write access."));
    $flows = new OauthFlows(clientCredentials: new ClientCredentialsFlow("https://example.com/token", $scopes));
    return new NamedSecurityScheme("oauth", new OAuth2($flows));
};

$testSecuritySchemes = function () use ($createOAuth) : void {
    SmokeTest::assertJson(
        '{"type":"apiKey","name":"api_key","in":"header"}',
        new ApiKey("api_key", ApiKeyLocation::HEADER)
    );

    SmokeTest::assertJson('{"type":"http","scheme":"basic"}', new HttpBasicAuth());
    SmokeTest::assertJson('{"type":"http","scheme":"bearer","bearerFormat":"JWT"}', new JwtBearer());

    $oauth = $createOAuth()->getSecurityScheme();

    $expected = '{"type":"oauth2","flows":{"clientCredentials":{"tokenUrl":"https://example.com/token",'
        . '"scopes":{"read":"Read access.","write":"Write access."}}}}';

    SmokeTest::assertJson($expected, $oauth);
    assert($oauth->hasScope("read") && !$oauth->hasScope("admin"));

    $createApiKeyWithoutName = function () : void {
        new ApiKey("", ApiKeyLocation::QUERY);
    };

    SmokeTest::expectException(InvalidValueException::class, $createApiKeyWithoutName, "name can't be an empty string");
};

$smoke->run("Security schemes", $testSecuritySchemes);

$testSecuritySchemeMap = function () : void {
    $jwt = new JwtBearer();
    $named = new NamedSecurityScheme("jwt", $jwt);
    assert($named->getName() === "jwt" && $named->getSecurityScheme() === $jwt);

    $map = new SecuritySchemeMap($named, new NamedSecurityScheme("basic", new HttpBasicAuth()));

    $expected = '{"jwt":{"type":"http","scheme":"bearer","bearerFormat":"JWT"},'
        . '"basic":{"type":"http","scheme":"basic"}}';

    SmokeTest::assertJson($expected, $map);

    $createWithDuplicateNames = function () use ($named) : void {
        new SecuritySchemeMap($named, new NamedSecurityScheme("jwt", new HttpBasicAuth()));
    };

    SmokeTest::expectException(
        DuplicateKeyException::class,
        $createWithDuplicateNames,
        'a security scheme named "jwt"'
    );

    $createWithBadName = function () : void {
        new NamedSecurityScheme("my scheme", new JwtBearer());
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithBadName, "can only contain letters, numbers");
};

$smoke->run("SecuritySchemeMap and NamedSecurityScheme", $testSecuritySchemeMap);

$testSecurityRequirement = function () use ($createOAuth) : void {
    $oauth = $createOAuth();
    $apiKey = new NamedSecurityScheme("apiKey", new ApiKey("api_key", ApiKeyLocation::HEADER));
    SmokeTest::assertJson('{"oauth":["read"]}', $oauth->createSecurityRequirement("read", "read"));
    SmokeTest::assertJson('{"apiKey":[]}', $apiKey->createSecurityRequirement());

    // Every scheme in a requirement is needed at once.
    $both = new SecurityRequirement(new RequiredScheme($oauth, "read", "write"), new RequiredScheme($apiKey));
    SmokeTest::assertJson('{"oauth":["read","write"],"apiKey":[]}', $both);
    assert($both->getSchemeNames() === ["oauth", "apiKey"]);
    SmokeTest::assertJson('{}', new SecurityRequirement());

    $requireUnofferedScope = function () use ($oauth) : void {
        $oauth->createSecurityRequirement("admin");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $requireUnofferedScope,
        "flows don't offer the \"admin\" scope"
    );

    $requireScopeOfApiKey = function () use ($apiKey) : void {
        new RequiredScheme($apiKey, "read");
    };

    SmokeTest::expectException(
        InvalidValueException::class,
        $requireScopeOfApiKey,
        "doesn't have scopes, as only OAuth2 and OpenIdConnect schemes do"
    );

    $requireSameSchemeTwice = function () use ($apiKey) : void {
        new SecurityRequirement(new RequiredScheme($apiKey), new RequiredScheme($apiKey));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $requireSameSchemeTwice, 'the "apiKey" security scheme');

    // Any one of the requirements in a collection is enough.
    $either = new SecurityRequirementCollection(
        $oauth->createSecurityRequirement("read"),
        $apiKey->createSecurityRequirement()
    );

    SmokeTest::assertJson('[{"oauth":["read"]},{"apiKey":[]}]', $either);
    assert($either->getSchemeNames() === ["oauth", "apiKey"]);
    SmokeTest::assertJson('[]', new SecurityRequirementCollection());
};

$smoke->run("SecurityRequirement and SecurityRequirementCollection", $testSecurityRequirement);

$testOtherSecuritySchemes = function () : void {
    SmokeTest::assertJson('{"type":"http","scheme":"bearer"}', new HttpBearer());
    SmokeTest::assertJson('{"type":"http","scheme":"bearer","bearerFormat":"opaque"}', new HttpBearer("opaque"));
    SmokeTest::assertJson('{"type":"http","scheme":"digest"}', new HttpAuth(HttpAuthScheme::DIGEST));

    $openIdUrl = "https://example.com/.well-known/openid-configuration";
    $openId = new OpenIdConnect($openIdUrl, "Log in with Example.");
    $expected = '{"type":"openIdConnect","description":"Log in with Example.","openIdConnectUrl":"' . $openIdUrl . '"}';
    SmokeTest::assertJson($expected, $openId);

    // Every scheme can have a description and extensions.
    $extensions = new ExtensionMap(new Extension("x-a", 1));
    $flows = new OauthFlows(implicit: new ImplicitFlow("https://example.com/auth", new ScopeMap()));

    $schemes = [
        new ApiKey("key", ApiKeyLocation::QUERY, "A key.", $extensions),
        new HttpBasicAuth("A key.", $extensions),
        new JwtBearer("A key.", $extensions),
        new HttpBearer(null, "A key.", $extensions),
        new HttpAuth(HttpAuthScheme::NEGOTIATE, "A key.", $extensions),
        new OpenIdConnect($openIdUrl, "A key.", $extensions),
        new OAuth2($flows, "A key.", $extensions),
    ];

    foreach ($schemes as $scheme)
    {
        $fields = $scheme->jsonSerialize();
        assert($fields->description === "A key." && $fields->{"x-a"} === 1, get_class($scheme));
    }

    // OpenID Connect's scopes are in its discovery document, so any can be required.
    $namedOpenId = new NamedSecurityScheme("openId", $openId);
    $requirement = $namedOpenId->createSecurityRequirement("profile", "email");
    SmokeTest::assertJson('{"openId":["profile","email"]}', $requirement);

    $badUrl = function () : void {
        new OpenIdConnect("not a url");
    };

    SmokeTest::expectException(InvalidValueException::class, $badUrl, "The OpenID Connect URL must be a URL");

    $emptyBearerFormat = function () : void {
        new HttpBearer("");
    };

    SmokeTest::expectException(InvalidValueException::class, $emptyBearerFormat, "bearer format can't be an empty");
};

$smoke->run("HttpBearer, HttpAuth, OpenIdConnect and scheme descriptions", $testOtherSecuritySchemes);
