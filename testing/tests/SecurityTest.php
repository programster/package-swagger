<?php

/*
 * Smoke tests for the security schemes (ApiKey, HttpBasicAuth, JwtBearer, ImplicitOauth2 and NoSecurity),
 * NamedSecurityScheme, SecuritySchemeMap, SecurityRequirement and SecurityRequirementCollection. These are run by
 * testing/main.php, which provides $smoke.
 */

use Programster\Swagger\OAuth\ImplicitFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\ImplicitOauth2;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\NoSecurity;
use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Testing\SmokeTest;

/** @var SmokeTest $smoke */

$testSecuritySchemes = function () : void {
    $apiKey = new ApiKey("api_key", ParameterLocation::HEADER);
    SmokeTest::assertJson('{"type":"apiKey","name":"api_key","in":"header"}', $apiKey);
    SmokeTest::assertJson('{"type":"http","scheme":"basic"}', new HttpBasicAuth());
    SmokeTest::assertJson('{"type":"http","scheme":"bearer","bearerFormat":"JWT"}', new JwtBearer());
    SmokeTest::assertJson('[]', new NoSecurity());

    // This used to fail to load, as OauthFlows implemented a JsonSerializable interface that doesn't exist.
    $flow = new ImplicitFlow("https://example.com/auth", new ScopeMap(new Scope("read", "Read access.")));
    $oauth = new ImplicitOauth2(new OauthFlows(implicit: $flow));

    $expected = '{"type":"oauth2","flows":{"implicit":{"authorizationUrl":"https://example.com/auth",'
        . '"scopes":{"read":"Read access."}}}}';

    SmokeTest::assertJson($expected, $oauth);
};

$smoke->run("Security schemes", $testSecuritySchemes);

$testSecuritySchemeMap = function () : void {
    $jwt = new JwtBearer();
    $named = new NamedSecurityScheme("jwt", $jwt);
    assert($named->getName() === "jwt");
    assert($named->getSecurityScheme() === $jwt);
    SmokeTest::assertJson('{"jwt":["read"]}', $named->createSecurityRequirement(["read"]));

    $map = new SecuritySchemeMap($named, new NamedSecurityScheme("basic", new HttpBasicAuth()));

    $expected = '{"jwt":{"type":"http","scheme":"bearer","bearerFormat":"JWT"},'
        . '"basic":{"type":"http","scheme":"basic"}}';

    SmokeTest::assertJson($expected, $map);
    assert(array_keys($map->toArray()) === ["jwt", "basic"]);

    // This never used to catch duplicate names, as it checked the property before it was set.
    $createWithDuplicateNames = function () use ($named) : void {
        new SecuritySchemeMap($named, new NamedSecurityScheme("jwt", new HttpBasicAuth()));
    };

    SmokeTest::expectException(\Exception::class, $createWithDuplicateNames, "same name: jwt");
};

$smoke->run("SecuritySchemeMap and NamedSecurityScheme", $testSecuritySchemeMap);

$testSecurityRequirement = function () : void {
    $requirement = new SecurityRequirement("oauth", ["read"]);
    SmokeTest::assertJson('{"oauth":["read"]}', $requirement);

    // add() used to add the scheme under an empty name, and the getters used to crash.
    $requirement->add("apiKey", []);
    SmokeTest::assertJson('{"oauth":["read"],"apiKey":[]}', $requirement);
    assert($requirement->getSchemeName() === "oauth");
    assert($requirement->getScopes() === ["read"]);

    $addDuplicate = function () use ($requirement) : void {
        $requirement->add("apiKey", []);
    };

    SmokeTest::expectException(\Exception::class, $addDuplicate, "Duplicate scheme name in SecurityRequirement");

    $jwtRequirement = new SecurityRequirement("jwt", []);
    $collection = new SecurityRequirementCollection($jwtRequirement, new SecurityRequirement("oauth", ["read"]));
    SmokeTest::assertJson('[{"jwt":[]},{"oauth":["read"]}]', $collection);
};

$smoke->run("SecurityRequirement and SecurityRequirementCollection", $testSecurityRequirement);
