# Security

- [Overview](#overview)
- [Security schemes](#security-schemes)
    - [API keys](#api-keys)
    - [HTTP basic authentication and bearer tokens](#http-basic-authentication-and-bearer-tokens)
    - [Other HTTP authentication schemes](#other-http-authentication-schemes)
    - [OAuth 2](#oauth-2)
    - [OpenID Connect](#openid-connect)
- [Adding the schemes to the document](#adding-the-schemes-to-the-document)
- [Requiring security](#requiring-security)
    - [One scheme](#one-scheme)
    - [Several schemes at once](#several-schemes-at-once)
    - [Any one of several](#any-one-of-several)
    - [Optional security](#optional-security)
- [Security for an operation](#security-for-an-operation)
- [What is checked](#what-is-checked)

## Overview
Security has two parts:

1. **Security schemes** describe how a client can authenticate, e.g. with an API key in a header. They are defined
   in the components, under a name.
2. **Security requirements** say which schemes the operations need. The document has requirements for every
   operation, and an operation can have its own instead.

## Security schemes
The schemes are in the `Programster\Swagger\Security` namespace. Every scheme can have a `description` (in which
CommonMark syntax may be used), and [specification extensions](extensions.md).

### API keys
An API key is sent in a header, the query string or a cookie, under a name:

```php
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\ApiKeyLocation;

$apiKey = new ApiKey("X-API-Key", ApiKeyLocation::HEADER);
```

`ApiKeyLocation` only has `HEADER`, `QUERY` and `COOKIE`, as an API key can't be part of the path.

### HTTP basic authentication and bearer tokens

```php
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\JwtBearer;

$basic = new HttpBasicAuth();

// A JWT sent as a bearer token, i.e. "Authorization: Bearer <token>".
$jwt = new JwtBearer(description: "A token from the /login endpoint.");
```

For a bearer token that isn't a JWT, use `HttpBearer`, which can be given a hint of the token's format, as it is
only for documentation:

```php
use Programster\Swagger\Security\HttpBearer;

$opaqueToken = new HttpBearer();
$macaroon = new HttpBearer(bearerFormat: "macaroon");
```

### Other HTTP authentication schemes
`HttpAuth` is for the other HTTP authentication schemes in the
[IANA registry](https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml), such as digest. They are
the cases of `HttpAuthScheme`, which doesn't have basic or bearer, as they have their own classes above:

```php
use Programster\Swagger\Security\HttpAuth;
use Programster\Swagger\Security\HttpAuthScheme;

$digest = new HttpAuth(HttpAuthScheme::DIGEST);
```

### OAuth 2
An `OAuth2` scheme has one or more flows, which are the ways that a client can get an access token. Each flow only
has the URLs that OpenAPI gives it:

| Flow | Authorization URL | Token URL | For |
|---|---|---|---|
| `ImplicitFlow` | ✓ | | Clients that get the token straight after the user authorizes them. |
| `AuthorizationCodeFlow` | ✓ | ✓ | Clients that exchange a code, which they get when the user authorizes them, for a token. |
| `PasswordFlow` | | ✓ | Clients that exchange the user's username and password for a token. |
| `ClientCredentialsFlow` | | ✓ | Clients that get a token with their own credentials, rather than a user's. |

Every flow has the scopes it offers, which may be none, and can have a refresh URL:

```php
use Programster\Swagger\OAuth\AuthorizationCodeFlow;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\Security\OAuth2;

$scopes = new ScopeMap(
    new Scope("users:read", "Read users."),
    new Scope("users:write", "Create and change users."),
);

$oauth = new OAuth2(new OauthFlows(
    authorizationCode: new AuthorizationCodeFlow(
        authorizationUrl: "https://auth.example.com/authorize",
        tokenUrl: "https://auth.example.com/token",
        scopes: $scopes,
        refreshUrl: "https://auth.example.com/refresh",
    ),
    clientCredentials: new ClientCredentialsFlow("https://auth.example.com/token", $scopes),
));
```

`OauthFlows` needs at least one flow. Every URL is checked, so a typo such as `htps://` throws an
`InvalidValueException`.

### OpenID Connect
An `OpenIdConnect` scheme is given the URL of its discovery document, which says everything else about it,
including the scopes it offers:

```php
use Programster\Swagger\Security\OpenIdConnect;

$openId = new OpenIdConnect("https://auth.example.com/.well-known/openid-configuration");
```

A requirement for an OpenID Connect scheme can ask for scopes, as one for OAuth 2 can. As the scopes are in the
discovery document, the package can't check that they are offered.

## Adding the schemes to the document
Each scheme is given a name with a `NamedSecurityScheme`, and they go in the components' `SecuritySchemeMap`:

```php
use Programster\Swagger\Components;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\SecuritySchemeMap;

$namedApiKey = new NamedSecurityScheme("apiKey", $apiKey);
$namedOauth = new NamedSecurityScheme("oauth", $oauth);

$components = new Components(securitySchemes: new SecuritySchemeMap($namedApiKey, $namedOauth));
```

## Requiring security
The requirements are created from the named schemes, so a scheme's name can't be mistyped.

### One scheme
`createSecurityRequirement()` creates a requirement for just that scheme, with the scopes it needs, if it is OAuth2:

```php
use Programster\Swagger\Security\SecurityRequirementCollection;

$needsApiKey = new SecurityRequirementCollection($namedApiKey->createSecurityRequirement());
$needsReadScope = new SecurityRequirementCollection($namedOauth->createSecurityRequirement("users:read"));
```

### Several schemes at once
A `SecurityRequirement` with several `RequiredScheme`s needs all of them at once:

```php
use Programster\Swagger\Security\RequiredScheme;
use Programster\Swagger\Security\SecurityRequirement;

// Both an API key and an OAuth token with the users:write scope.
$needsBoth = new SecurityRequirementCollection(new SecurityRequirement(
    new RequiredScheme($namedApiKey),
    new RequiredScheme($namedOauth, "users:write"),
));
```

### Any one of several
A `SecurityRequirementCollection` with several requirements needs any one of them:

```php
// Either an API key, or an OAuth token with the users:read scope.
$needsEither = new SecurityRequirementCollection(
    $namedApiKey->createSecurityRequirement(),
    $namedOauth->createSecurityRequirement("users:read"),
);
```

### Optional security
A requirement with no schemes is met without any security. Adding one makes security optional, e.g. for an operation
that returns more to a client that authenticates:

```php
$optional = new SecurityRequirementCollection($namedApiKey->createSecurityRequirement(), new SecurityRequirement());
```

## Security for an operation
The security given to the document is used for every operation that doesn't have its own:

```php
use Programster\Swagger\Info;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\Method;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;

$ok = new ResponseCollection(
    new MappedResponse(ResponseCode::OK, new Response("OK."))
);


$listUsers = new Operation(Method::GET, $ok);
$createUser = new Operation(Method::POST, $ok, security: $needsBoth);

// An empty collection means no security is needed, so this operation is public.
$health = new Operation(Method::GET, $ok, security: new SecurityRequirementCollection());

$document = new OpenApi(
    info: new Info("Users API", "1.0"),
    paths: new PathCollection(
        new Path(
            "/users",
            new PathItem(new OperationCollection($listUsers, $createUser))
        ),
        new Path(
            "/health",
            new PathItem(new OperationCollection($health))
        ),
    ),
    components: $components,
    security: $needsEither,
);
```

| An operation's `security` | Means |
|---|---|
| `null` (the default) | It uses the document's security. |
| A collection of requirements | It needs any one of them, instead of the document's. |
| An empty collection | It doesn't need any security, i.e. it's public. |

## What is checked
- Only OAuth2 and OpenIdConnect schemes have scopes, and a requirement for an OAuth2 scheme can only ask for the
  scopes that its flows offer:

```php
$typo = $namedOauth->createSecurityRequirement("user:read"); // Throws InvalidValueException
```

- A requirement can't have the same scheme twice.
- The document checks that every scheme that a requirement uses (in the document's security, or any operation's) is
  in the components, and throws an `InvalidDocumentException` naming the operation if one isn't:

```php
$noSchemes = new OpenApi(
    info: new Info("Users API", "1.0"),
    paths: new PathCollection(
        new Path(
            "/users",
            new PathItem(new OperationCollection($createUser))
        )
    ),
); // Throws InvalidDocumentException
```
