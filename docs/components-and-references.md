# Components and references

- [Components](#components)
- [Naming a component](#naming-a-component)
- [Referencing a component](#referencing-a-component)
- [The kinds of reference](#the-kinds-of-reference)
- [Referencing an example](#referencing-an-example)
- [Referencing another document](#referencing-another-document)

## Components
The components are things that are defined once, under a name, and can then be referenced from anywhere in the
document, rather than being written out wherever they're used. They are also where the security schemes are
defined.

```php
use Programster\Swagger\Components;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;

$user = new NamedSchema("User", new SchemaObject(properties: new PropertyMap(
    new Property("name", new SchemaString(), required: true),
)));

$components = new Components(schemas: new SchemaMap($user));
```

| Parameter | Type | Holds |
|---|---|---|
| `schemas` | `SchemaMap` | `NamedSchema`s |
| `responses` | `ResponseMap` | `NamedResponse`s |
| `parameters` | `ParameterMap` | `NamedParameter`s |
| `examples` | `ExampleMap` | `NamedExample`s |
| `requestBodies` | `RequestBodyMap` | `NamedRequestBody`s |
| `headers` | `HeaderMap` | `NamedHeader`s |
| `securitySchemes` | `SecuritySchemeMap` | `NamedSecurityScheme`s. See [Security](security.md). |
| `links` | `LinkMap` | `NamedLink`s |
| `callbacks` | `CallbackMap` | `NamedCallback`s |

The components are given to the document with `new OpenApi(..., components: $components)`. They're left out of the
JSON when there aren't any. They can also be given [specification extensions](extensions.md).

## Naming a component
Each component is given its name by wrapping it in its named class, such as a `NamedSchema` or `NamedParameter`.
OpenAPI only allows a component's name to contain letters, numbers, dots, dashes and underscores, which the named
classes check:

```php
$wrong = new NamedSchema("User Account", new SchemaObject()); // Throws InvalidValueException
```

Each map can only have one component with each name.

## Referencing a component
Every named class has a `createReference()` method, which creates a reference to it. Using that means the name can't
be mistyped:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Schemas\SchemaArray;

$oneUser = new ContentMap(new MediaType(MimeType::JSON, $user->createReference()));
$manyUsers = new ContentMap(new MediaType(MimeType::JSON, new SchemaArray($user->createReference())));
```

The reference is output as `{"$ref": "#/components/schemas/User"}`.

The package doesn't check that a referenced component is in the components, as a reference can be created before
the components are. [Redocly CLI](../README.md#validating-a-document) checks this when it validates the finished
document.

## The kinds of reference
Each kind of component has its own kind of reference, in the `Programster\Swagger\References` namespace. Each
implements the same interface as the thing it refers to, so it can be used anywhere one could, but not anywhere
else. A reference to a schema can't be used as a parameter, for instance.

| Reference | Can be used as a | Points at |
|---|---|---|
| `SchemaReference` | `SchemaInterface` | `#/components/schemas/...` |
| `ParameterReference` | `ParameterInterface` | `#/components/parameters/...` |
| `ResponseReference` | `ResponseInterface` | `#/components/responses/...` |
| `RequestBodyReference` | `RequestBodyInterface` | `#/components/requestBodies/...` |
| `HeaderReference` | `HeaderInterface` | `#/components/headers/...` |
| `ExampleReference` | the example of a `NamedExample` | `#/components/examples/...` |
| `LinkReference` | `LinkInterface` | `#/components/links/...` |
| `CallbackReference` | `CallbackInterface` | `#/components/callbacks/...` |
| `PathItemReference` | `PathItemInterface` | another document only, as OpenAPI 3.0 has no path items in the components |

A reference can also be created from just the component's name, e.g. for a component that is defined in another part
of your code:

```php
use Programster\Swagger\References\SchemaReference;

$sameUser = SchemaReference::createToComponent("User");
```

## Referencing an example
Examples are named wherever they are, so a `NamedExample` is used both in the components and in a media type's (or a
parameter's) `ExampleMap`. To use one from the components, give the `NamedExample` a reference rather than an
example:

```php
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;

$bob = new NamedExample("Bob", new ValueExample(['name' => "Bob"]), summary: "A typical user.");
$componentsWithExample = new Components(examples: new ExampleMap($bob));

$userContent = new ContentMap(new MediaType(
    mimeType: MimeType::JSON,
    schema: $user->createReference(),
    example: new ExampleMap(new NamedExample("bob", $bob->createReference())),
));
```

A `NamedExample` with a reference can't have a summary or description, as OpenAPI ignores anything beside a
reference. The example it points at has them instead.

## Referencing another document
A reference can also point at another document, e.g. a schema that is shared between several APIs:

```php
// The whole of another document, which is the schema itself.
$address = SchemaReference::createToDocument("schemas/Address.json");

// Something inside another document, by its JSON pointer.
$country = SchemaReference::createToDocumentPath("https://example.com/definitions.json", "/Country");
```

A relative path is relative to where the document is served from. The second reference is output as
`{"$ref": "https://example.com/definitions.json#/Country"}`.

A path (or a callback) can be a reference to a path item in another document, e.g. for operations that several APIs
share. It's not possible to check that its parameters match the path's variables, as they are in the other document:

```php
use Programster\Swagger\Path;
use Programster\Swagger\References\PathItemReference;

$health = new Path("/health", PathItemReference::createToDocument("shared/health.json"));
```
