<?php

/*
 * Smoke tests for the OAuth flows, OauthFlows, Scope and ScopeMap. These are run by testing/main.php, which provides
 * $smoke.
 */

use Programster\Swagger\OAuth\AuthorizationCodeFlow;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\ImplicitFlow;
use Programster\Swagger\OAuth\OauthFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\PasswordFlow;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$createScopes = function () : ScopeMap {
    return new ScopeMap(new Scope("read", "Read access."), new Scope("write", "Write access."));
};

$testScopes = function () use ($createScopes) : void {
    // The getters used to call themselves forever.
    $scope = new Scope("read", "Read access.");
    assert($scope->getName() === "read");
    assert($scope->getDescription() === "Read access.");

    // This used to call a toArray() method that didn't exist.
    SmokeTest::assertJson('{"read":"Read access.","write":"Write access."}', $createScopes());

    $createWithDuplicateNames = function () : void {
        new ScopeMap(new Scope("read", "Read."), new Scope("read", "Read again."));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "Duplicate scope name: read");
};

$smoke->run("Scope and ScopeMap", $testScopes);

$testFlowsWithTokenUrl = function () use ($createScopes) : void {
    $flowClasses = [AuthorizationCodeFlow::class, ClientCredentialsFlow::class, PasswordFlow::class];

    foreach ($flowClasses as $flowClass)
    {
        $flow = new $flowClass("https://example.com/auth", "https://example.com/token", $createScopes());

        $expected = <<<'JSON'
            {
                "authorizationUrl": "https://example.com/auth",
                "tokenUrl": "https://example.com/token",
                "scopes": {"read": "Read access.", "write": "Write access."}
            }
            JSON;

        SmokeTest::assertJson($expected, $flow);

        $withRefresh = new $flowClass(
            "https://example.com/auth",
            "https://example.com/token",
            $createScopes(),
            "https://example.com/refresh"
        );

        assert($withRefresh->jsonSerialize()['refreshUrl'] === "https://example.com/refresh", $flowClass);

        $badUrls = [
            ["not a url", "https://example.com/token", null],
            ["https://example.com/auth", "not a url", null],
            ["https://example.com/auth", "https://example.com/token", "not a url"],
        ];

        foreach ($badUrls as [$authorizationUrl, $tokenUrl, $refreshUrl])
        {
            $createWithBadUrl = function () use ($flowClass, $authorizationUrl, $tokenUrl, $refreshUrl) : void {
                new $flowClass($authorizationUrl, $tokenUrl, new ScopeMap(), $refreshUrl);
            };

            SmokeTest::expectException(\Exception::class, $createWithBadUrl, "not a url is not a valid URL");
        }
    }
};

$smoke->run("AuthorizationCodeFlow, ClientCredentialsFlow and PasswordFlow", $testFlowsWithTokenUrl);

$testImplicitFlow = function () use ($createScopes) : void {
    // This used to expect a ScopeMap class that doesn't exist, so no ScopeMap could be passed to it.
    $flow = new ImplicitFlow("https://example.com/auth", $createScopes(), "https://example.com/refresh");

    $expected = <<<'JSON'
        {
            "authorizationUrl": "https://example.com/auth",
            "scopes": {"read": "Read access.", "write": "Write access."},
            "refreshUrl": "https://example.com/refresh"
        }
        JSON;

    SmokeTest::assertJson($expected, $flow);

    $createWithBadUrl = function () : void {
        new ImplicitFlow("not a url", new ScopeMap());
    };

    SmokeTest::expectException(\Exception::class, $createWithBadUrl, "not a url is not a valid URL");

    $createWithBadRefreshUrl = function () : void {
        new ImplicitFlow("https://example.com/auth", new ScopeMap(), "not a url");
    };

    SmokeTest::expectException(\Exception::class, $createWithBadRefreshUrl, "not a url is not a valid URL");
};

$smoke->run("ImplicitFlow", $testImplicitFlow);

$testOauthFlow = function () : void {
    $authUrl = "https://example.com/auth";
    $tokenUrl = "https://example.com/token";
    $flow = new OauthFlow($authUrl, $tokenUrl, null, "read");

    $expected = '{"authorizationUrl":"https://example.com/auth","tokenUrl":"https://example.com/token",'
        . '"scopes":"read"}';

    SmokeTest::assertJson($expected, $flow);

    $withRefresh = new OauthFlow($authUrl, $tokenUrl, "https://example.com/refresh", "read");
    assert($withRefresh->jsonSerialize()['refreshUrl'] === "https://example.com/refresh");
};

$smoke->run("OauthFlow", $testOauthFlow);

$testOauthFlows = function () use ($createScopes) : void {
    $authUrl = "https://example.com/auth";
    $tokenUrl = "https://example.com/token";
    $implicit = new ImplicitFlow($authUrl, $createScopes());
    $password = new PasswordFlow($authUrl, $tokenUrl, $createScopes());
    $clientCredentials = new ClientCredentialsFlow($authUrl, $tokenUrl, $createScopes());
    $authorizationCode = new AuthorizationCodeFlow($authUrl, $tokenUrl, $createScopes());

    // Every flow used to be output under the "clientCredentials" key, so only the last one given was kept.
    $flows = new OauthFlows($implicit, $password, $clientCredentials, $authorizationCode);
    $flowsArray = json_decode(json_encode($flows), true);
    assert(array_keys($flowsArray) === ["implicit", "password", "clientCredentials", "authorizationCode"]);
    assert($flowsArray['implicit'] === json_decode(json_encode($implicit), true));
    assert($flowsArray['authorizationCode'] === json_decode(json_encode($authorizationCode), true));

    $onlyPassword = new OauthFlows(password: $password);
    assert(array_keys($onlyPassword->jsonSerialize()) === ["password"]);

    $createWithoutFlows = function () : void {
        new OauthFlows();
    };

    SmokeTest::expectException(\Exception::class, $createWithoutFlows, "at least one of the types of flows");
};

$smoke->run("OauthFlows", $testOauthFlows);
