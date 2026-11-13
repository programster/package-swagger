# Upgrading from version 3

- [Overview](#overview)
- [Requirements](#requirements)
- [Changes that affect everything](#changes-that-affect-everything)
- [References](#references)
- [Paths and operations](#paths-and-operations)
- [Parameters and headers](#parameters-and-headers)
- [Content, request bodies and examples](#content-request-bodies-and-examples)
- [Responses and links](#responses-and-links)
- [Schemas](#schemas)
- [Servers](#servers)
- [Security and OAuth](#security-and-oauth)
- [The document](#the-document)
- [Changes to the output](#changes-to-the-output)
- [New in version 4](#new-in-version-4)

## Overview
Version 4 is a redesign that makes mistakes impossible to write, or catches them as the document is built, and makes
the output valid OpenAPI 3.0.3 where version 3's wasn't. Most classes have kept their names, but many constructors
have changed. This page lists every change, with the version 3 code and what replaces it.

## Requirements
- PHP 8.2 or later, as every class is now a `readonly` class. Version 3 supported PHP 8.1.
- [ramsey/collection](https://github.com/ramsey/collection) ^2.1, which Composer installs.

## Changes that affect everything
- **Every class is `final`**, and every value class is `readonly`.
- **The maps take their items in their constructor.** `add()` and `addOrReplace()` are gone from `ContentMap`,
  `LinkMap`, `RequestBodyMap`, `ServerVariableMap` and `ExampleMap`, which now take their items as a variadic. The
  maps and collections are ramsey/collection collections, so items can still be added with `add()`, but one whose key
  is taken throws a `DuplicateKeyException`, rather than replacing it.
- **The `ArrayObject` collections** (`PathCollection`, `OperationCollection`, `ParameterCollection` and
  `ServerCollection`) are ramsey/collection collections, so use `add()` rather than `append()`.
- **`toArray()` is gone** from every class, as it returned some objects and some arrays. Every class is
  `JsonSerializable`, so use `json_encode()` (or `jsonSerialize()`) instead.
- **Optional collections default to empty ones, rather than null**, e.g. an `Operation`'s `parameters` defaults to
  `new ParameterCollection()`. Passing `null` for one is now a `TypeError`.
- **Flags are `bool`, rather than `?bool`**, e.g. `deprecated: false` rather than `deprecated: null`. False is
  OpenAPI's default for each, so false isn't output.
- **The exceptions are the package's own**, all extending `SwaggerException`, which extends `\Exception`, so code that
  catches `\Exception` still catches them. See [Errors and checks](errors.md).
- **Many more mistakes are caught**, so a document that version 3 output may now throw. Each exception says what to
  fix. See [Errors and checks](errors.md) for everything that is checked.

## References
The `Reference` class is replaced by a reference class for each kind of component, which can only be used where that
kind of thing can, and always points at the right part of the components:

| Version 3 | Version 4 |
|---|---|
| `Reference::createReferenceObject("Pet")` | `SchemaReference::createToComponent("Pet")`, or better, `$namedSchema->createReference()` |
| `Reference::createRelativeSchemaDocument("Pet.json")` | `SchemaReference::createToDocument("Pet.json")` |
| `Reference::createRelativeDocumentEmbeddedSchema("defs.json", "Pet")` | `SchemaReference::createToDocumentPath("defs.json", "/Pet")` |
| `new Reference("#/components/parameters/id")` | `ParameterReference::createToComponent("id")` |

The others are `ResponseReference`, `RequestBodyReference`, `HeaderReference`, `ExampleReference`, `LinkReference`,
`CallbackReference` and `PathItemReference`, which are all in the `Programster\Swagger\References` namespace. Every
named component class (e.g. `NamedSchema`, `NamedParameter`) has a `createReference()` method, so its name doesn't need
to be typed again.

The unions with `Reference` are replaced by interfaces, which the thing and its reference both implement:

| Version 3 | Version 4 |
|---|---|
| `SchemaInterface \| Reference` | `SchemaInterface` |
| `Parameter \| Reference` | `ParameterInterface` |
| `Response \| Reference` | `ResponseInterface` |
| `RequestBody \| Reference` | `RequestBodyInterface` |
| `Link \| Reference` | `LinkInterface` |
| `Callback \| Reference` | `CallbackInterface` |
| `Header` | `HeaderInterface` |
| `PathItem` (new in version 4) | `PathItemInterface` |

## Paths and operations
A `Path`'s operations and shared settings are now in a `PathItem`, which callbacks use too:

```php
// Version 3
$path = new Path("/users", new OperationCollection($getUsers), summary: "Users.");
```

```php
use Programster\Swagger\MappedResponse;
use Programster\Swagger\Method;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Path;
use Programster\Swagger\PathItem;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;

$getUsers = new Operation(
    Method::GET,
    new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response("OK."))
    )
);

$path = new Path(
    "/users",
    new PathItem(new OperationCollection($getUsers), summary: "Users.")
);
```

- `Path`'s `ref` parameter is gone.
- Every variable in a path (e.g. the `{id}` in `/users/{id}`) must have a path parameter, and every path parameter
  must be in the path.
- `PathCollection` now rejects two paths that are the same, which `OpenApi` used to.
- `Operation`'s `externalDocs` is an `ExternalDocumentation`, rather than a string, as OpenAPI requires an object.
- `Operation`'s `security` is a `SecurityRequirementCollection`. A single `SecurityRequirement` is no longer accepted,
  as it was output as an object, rather than the array that OpenAPI requires.
- `Operation`'s `callbacks` is a `CallbackMap`, which replaces `Callbacks`.
- `Operation`'s `tags` defaults to `[]`, rather than null.
- A GET, HEAD, DELETE or TRACE operation can't have a request body.
- `ResponseCollection` needs at least one response, as its first parameter isn't variadic.
- `Callback` takes a `PathItem`, rather than a `Path`, as the callback's expression is its URL.

## Parameters and headers
`Parameter::createFromSchema()` takes the schema before `required`, as `required` is now optional:

```php
// Version 3
$id = Parameter::createFromSchema("id", ParameterLocation::PATH, true, new SchemaString());
$sort = Parameter::createFromSchema("sort", ParameterLocation::QUERY, false, new SchemaString(), style: "form", example: "name");
```

```php
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Style;

$id = Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString());
$sort = Parameter::createFromSchema(
    "sort",
    ParameterLocation::QUERY,
    new SchemaString(),
    style: Style::FORM,
    example: new ValueExample("name")
);
```

- `required` defaults to true for path parameters (which must be required) and false for the rest.
- `createFromContentMap()` also takes the content before `required`.
- `style` is only a `Style`. A string is no longer accepted.
- `example` is a `ValueExample` or an `ExampleMap`, rather than anything.
- `deprecated`, `allowEmptyValue` and `allowReserved` are `bool`s.
- `ParameterCollection` takes `ParameterInterface`s, and rejects two parameters with the same name and location.
- A `Header` no longer has a name. A response's (or an encoding's) headers are a `HeaderCollection`, in which a
  `MappedHeader` gives each its HTTP name, so that a header in the components (which are under their component
  names) can be referenced by different names. `HeaderMap` is now the components' headers, of `NamedHeader`s:

```php
// Version 3
$headers = new HeaderMap(Header::createFromSchema("X-Rate-Limit", true, new SchemaInt()));
```

```php
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\Schemas\SchemaInt;

$headers = new HeaderCollection(
    new MappedHeader(
        "X-Rate-Limit",
        Header::createFromSchema(new SchemaInt(), true)
    )
);
```

- `Header::createFromSchema()` no longer has `allowEmptyValue` or `allowReserved`, as they can't be used for headers.
  It takes the schema before `required`, its `required` and `deprecated` are `bool`s, and its `example` is a
  `ValueExample` or an `ExampleMap`.

## Content, request bodies and examples
A `MediaType` now has its mime type, and a `ContentMap` takes its media types as a variadic:

```php
// Version 3
$content = new ContentMap(MimeType::JSON, new MediaType($schema));
$content->add("application/xml", new MediaType($schema));
```

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;

$schema = new SchemaString();
$content = new ContentMap(new MediaType(MimeType::JSON, $schema), new MediaType(MimeType::XML, $schema));
```

- `MimeType` has more cases (e.g. `XML`, `PDF`, `PNG`), and anything that takes one also takes a string, for one
  that it doesn't have.
- `MediaType`'s schema is optional.
- `MediaType`'s `example` is a `ValueExample` or an `ExampleMap`. An `ExternalValueExample` can only go in an
  `ExampleMap`, as OpenAPI doesn't allow one on its own.
- `MediaType`'s `encoding` is an `EncodingMap` of `Encoding`s, each for a property of the schema, as OpenAPI requires,
  rather than a single `Encoding`. An encoding can only be given for multipart and form content.
- `Encoding` takes the name of the property it is for, and everything else is optional.
- `RequestBody`'s `required` is a `bool`.
- `RequestBodyMap` takes `NamedRequestBody`s: `new RequestBodyMap(new NamedRequestBody("User", $body))`.
- `ExampleMap` no longer has `add()`, `addOrReplace()` or `toArray()`.
- `ExternalValueExample`'s parameter is called `url`, rather than `externalValue`.

## Responses and links

```php
// Version 3
$response = new Response("A user.", contentMap: $content, headerMap: $headers, linkMap: new LinkMap("group", $link));
```

```php
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\NamedLink;
use Programster\Swagger\Response;

$link = Link::createFromOperationId("getGroup", ['id' => '$response.body#/groupId']);
$response = new Response(
    "A user.",
    content: $content,
    headers: $headers,
    links: new LinkMap(new NamedLink("group", $link))
);
```

- `Response`'s parameters are named `content`, `headers` and `links`, after the fields they fill in, and the content
  is optional, for a response without a body. Its `headers` is a `HeaderCollection`.
- `MappedResponse` also takes an int, for a response code that `ResponseCode` doesn't have, so its
  `getResponseCode()` is replaced by `getCode()`, which returns the code as a string (e.g. `"404"`).
- `Link` is created with `Link::createFromOperationId()` or `Link::createFromOperationRef()`, as it must have one of
  them, but not both.
- `LinkMap` takes `NamedLink`s.
- The components' `responses` is a `ResponseMap` of `NamedResponse`s, under their names. Version 3 used a
  `ResponseCollection`, which is under response codes, so it produced responses called "200" and so on.

## Schemas
- `Format` is now `Programster\Swagger\Schemas\StringFormat`, and has OpenAPI's `PASSWORD`, `BYTE` and `BINARY`.
- `Type` is now in the `Schemas` namespace, and has no `NULL`, as OpenAPI 3.0 doesn't have a null type. Use
  `nullable: true` on any schema instead.
- `SchemaInt` and `SchemaNumber`'s `exclusiveMinimum` and `exclusiveMaximum` are flags on the minimum and maximum,
  rather than numbers, as they are in OpenAPI 3.0. Their parameters now start in the order `description`,
  `minimum`, `maximum`, `exclusiveMinimum`, `exclusiveMaximum`, `multipleOf`, `default`, `format`, `nullable`, and
  they have a `format` (`IntegerFormat` and `NumberFormat`). Named arguments are the safest way to pass them.
- `SchemaArray`'s `items` is required, and its first parameter, as OpenAPI requires it. It can be a reference.
  `minContains` and `maxContains` are gone, as OpenAPI 3.0 doesn't have them, and `uniqueItems` is a `bool`.
- `SchemaObject`'s `properties` is a `PropertyMap` of `Property`s, each of which says whether it is required, so
  `requiredProperties` is gone:

```php
// Version 3
$user = new SchemaObject(
    properties: new SchemaMap(new NamedSchema("id", new SchemaString()), new NamedSchema("name", new SchemaString())),
    requiredProperties: ["id"],
);
```

```php
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;

$user = new SchemaObject(properties: new PropertyMap(
    new Property("id", new SchemaString(), required: true),
    new Property("name", new SchemaString()),
));
```

- `NamedSchema` and `SchemaMap` are now only for the components, so a `NamedSchema`'s name must be a valid component
  name.
- `SchemaBool`, `SchemaArray` and `SchemaObject` can be `nullable`, as the others could.

## Servers
- `Server`'s `description` and `variables` are optional.
- `ServerVariable` has its name, as its first parameter, and `ServerVariableMap` takes them as a variadic:

```php
// Version 3
$variables = new ServerVariableMap("port", new ServerVariable("443"));
```

```php
use Programster\Swagger\Server;
use Programster\Swagger\ServerVariable;
use Programster\Swagger\ServerVariableMap;

$server = new Server(
    "https://api.example.com:{port}",
    variables: new ServerVariableMap(new ServerVariable("port", "443"))
);
```

- Every variable in the URL must have a `ServerVariable`, and the other way round, and a variable's default must be
  one of its allowed values.

## Security and OAuth
- `InterfaceSecurityScheme` is now `SecuritySchemeInterface`.
- `ApiKey`'s location is an `ApiKeyLocation`, which has no path, as an API key can't go there.
- `ImplicitOauth2` is now `OAuth2`, as it takes any of the flows, not just the implicit one.
- `JwtBearer` and `HttpBasicAuth` take an optional description, as every security scheme now does.
- `NoSecurity` is gone. To make an operation public, give it an empty `SecurityRequirementCollection`.
- `PasswordFlow` and `ClientCredentialsFlow` no longer take an `authorizationUrl`, as those flows don't have one, so
  the token URL is their first parameter.
- `OauthFlow` is gone, as the specific flows replace it.
- `SecurityRequirement` is made from `RequiredScheme`s, each of which is made from a `NamedSecurityScheme`, so a
  scheme's name can't be mistyped. Its `add()`, `getSchemeName()` and `getScopes()` are gone.
- `NamedSecurityScheme::createSecurityRequirement()` takes the scopes as a variadic, and they must be offered by the
  scheme's flows:

```php
// Version 3
$requirement = $namedScheme->createSecurityRequirement(["read"]);
$both = new SecurityRequirement("oauth", ["read"]);
$both->add("apiKey", []);
```

```php
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\ApiKeyLocation;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\OAuth2;
use Programster\Swagger\Security\RequiredScheme;
use Programster\Swagger\Security\SecurityRequirement;

$flow = new ClientCredentialsFlow(
    "https://auth.example.com/token",
    new ScopeMap(new Scope("read", "Read access."))
);

$oauth = new NamedSecurityScheme(
    "oauth",
    new OAuth2(new OauthFlows(clientCredentials: $flow))
);

$apiKey = new NamedSecurityScheme("apiKey", new ApiKey("X-API-Key", ApiKeyLocation::HEADER));

$requirement = $oauth->createSecurityRequirement("read");
$both = new SecurityRequirement(new RequiredScheme($oauth, "read"), new RequiredScheme($apiKey));
```

## The document
- `OpenApi`'s `servers`, `components` and `tags` default to empty ones, rather than null.
- `OpenApi::toArray()` is gone. Use `json_encode()`, or `json_decode($document->toJson(), true)` for an array.
- `OpenApi` checks that every security scheme that is used is in the components, and that operation IDs are unique.
- `Components`'s `callbacks` is a `CallbackMap`, its `responses` is a `ResponseMap`, and each defaults to an empty
  map.
- `OpenApi::OPENAPI_VERSION` is the version of OpenAPI that documents are in.

## Changes to the output
Some documents are output differently, to be valid OpenAPI:

- Empty maps (e.g. an OAuth flow's `scopes`) are output as `{}`, rather than `[]`.
- In YAML, response codes are quoted (e.g. `"200":`), and empty maps are `{}`.
- Each object's fields are output in the order the specification lists them, e.g. an `Info`'s `version` comes last.
- Flags that are false, such as `deprecated`, are left out, as false is OpenAPI's default.

## New in version 4
Version 4 also supports much more of OpenAPI 3.0.3 than version 3 did:

- **Combining schemas**: `SchemaAllOf`, `SchemaOneOf`, `SchemaAnyOf` and `SchemaNot`, with a `Discriminator`. See
  [Combining schemas](schemas.md#combining-schemas).
- **More schema options**: `enum` for strings and numbers, `additionalProperties` for maps, `title`, `example`,
  `deprecated`, `externalDocs` and `xml` on every schema, read-only and write-only properties, and `SchemaAnyValue`
  for a value of any type. See [Schemas](schemas.md).
- **More references**: headers and examples can be referenced from the components, and a path (or callback) can be a
  reference to a path item in another document. See [Components and references](components-and-references.md).
- **More response codes**: ranges such as `ResponseCode::ANY_CLIENT_ERROR` (4XX), the 1XX codes, and any other code
  as an int. See [Response codes](responses.md#response-codes).
- **More security schemes**: `OpenIdConnect`, `HttpBearer` for bearer tokens that aren't JWTs, and `HttpAuth` for
  the other HTTP authentication schemes, such as digest. See [Security](security.md).
- **Specification extensions**: an `x-` field can be added to any object that OpenAPI allows them on. See
  [Specification extensions](extensions.md).
