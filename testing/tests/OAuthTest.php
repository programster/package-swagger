<?php

/*
 * Smoke tests for the OAuth flows, OauthFlows, Scope and ScopeMap. These are run by testing/main.php, which provides
 * $smoke.
 */

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\OAuth\AuthorizationCodeFlow;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\ImplicitFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\PasswordFlow;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createScopes = function () : ScopeMap {
    return new ScopeMap(new Scope("read", "Read access."));
};

$testScopes = function () use ($createScopes) : void {
    $scope = new Scope("read", "Read access.");
    assert($scope->getName() === "read" && $scope->getDescription() === "Read access.");
    SmokeTest::assertJson('{"read":"Read access."}', $createScopes());

    // A flow may offer no scopes, which is output as {}.
    SmokeTest::assertJson('{}', new ScopeMap());

    $createWithDuplicateNames = function () : void {
        new ScopeMap(new Scope("read", "Read."), new Scope("read", "Read again."));
    };

    SmokeTest::expectException(DuplicateKeyException::class, $createWithDuplicateNames, 'a scope named "read"');
};

$smoke->run("Scope and ScopeMap", $testScopes);

$testFlows = function () use ($createScopes) : void {
    $auth = "https://example.com/auth";
    $token = "https://example.com/token";
    $refresh = "https://example.com/refresh";
    $scopes = '"scopes":{"read":"Read access."}';

    // Each flow only has the URLs that OpenAPI gives it.
    $implicit = new ImplicitFlow($auth, $createScopes(), $refresh);

    SmokeTest::assertJson(
        '{"authorizationUrl":"' . $auth . '","refreshUrl":"' . $refresh . '",' . $scopes . '}',
        $implicit
    );

    $password = new PasswordFlow($token, $createScopes());
    SmokeTest::assertJson('{"tokenUrl":"' . $token . '",' . $scopes . '}', $password);

    $clientCredentials = new ClientCredentialsFlow($token, $createScopes());
    SmokeTest::assertJson('{"tokenUrl":"' . $token . '",' . $scopes . '}', $clientCredentials);

    $authorizationCode = new AuthorizationCodeFlow($auth, $token, $createScopes());

    SmokeTest::assertJson(
        '{"authorizationUrl":"' . $auth . '","tokenUrl":"' . $token . '",' . $scopes . '}',
        $authorizationCode
    );

    assert($authorizationCode->getScopes()->hasKey("read"));

    $createImplicitWithBadUrl = function () use ($createScopes) : void {
        new ImplicitFlow("auth", $createScopes());
    };

    $createPasswordWithBadUrl = function () use ($createScopes) : void {
        new PasswordFlow("token", $createScopes());
    };

    $createClientCredentialsWithBadRefreshUrl = function () use ($createScopes, $token) : void {
        new ClientCredentialsFlow($token, $createScopes(), "refresh");
    };

    $createAuthorizationCodeWithBadUrl = function () use ($createScopes, $auth) : void {
        new AuthorizationCodeFlow($auth, "token", $createScopes());
    };

    $createWithBadUrls = [
        $createImplicitWithBadUrl,
        $createPasswordWithBadUrl,
        $createClientCredentialsWithBadRefreshUrl,
        $createAuthorizationCodeWithBadUrl,
    ];

    foreach ($createWithBadUrls as $createWithBadUrl)
    {
        SmokeTest::expectException(InvalidValueException::class, $createWithBadUrl, "must be a URL");
    }
};

$smoke->run("OAuth flows", $testFlows);

$testOauthFlows = function () use ($createScopes) : void {
    $implicit = new ImplicitFlow("https://example.com/auth", new ScopeMap(new Scope("admin", "Everything.")));
    $password = new PasswordFlow("https://example.com/token", $createScopes());
    $flows = new OauthFlows(implicit: $implicit, password: $password);
    assert(array_keys(get_object_vars($flows->jsonSerialize())) === ["implicit", "password"]);

    // A scope offered by any of the flows can be required.
    assert($flows->hasScope("admin") && $flows->hasScope("read") && !$flows->hasScope("write"));

    $createWithoutFlows = function () : void {
        new OauthFlows();
    };

    SmokeTest::expectException(InvalidValueException::class, $createWithoutFlows, "needs at least one flow");
};

$smoke->run("OauthFlows", $testOauthFlows);
