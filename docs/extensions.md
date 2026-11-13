# Specification extensions

- [What they are](#what-they-are)
- [Adding an extension](#adding-an-extension)
- [Where extensions can go](#where-extensions-can-go)
- [What is checked](#what-is-checked)

## What they are
A specification extension is an extra field, whose name starts with `x-`, that tools can use for things OpenAPI
doesn't cover. For example, [Redoc](https://redocly.com/docs/api-reference-docs/specification-extensions/) reads
`x-logo` from the info for a logo to show, and `x-tagGroups` from the document to group its tags, and API gateways
often read their settings from extensions on the operations.

## Adding an extension
An `Extension` has the field's name and value, and the extensions of an object go in an `ExtensionMap`, which is the
`extensions` parameter of each object that can have them:

```php
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Info;

$info = new Info(
    title: "Users API",
    version: "1.0.0",
    extensions: new ExtensionMap(
        new Extension("x-logo", ['url' => "https://example.com/logo.png", 'altText' => "Example Ltd"]),
        new Extension("x-audience", "public"),
    ),
);
```

Which outputs:

```json
{
    "title": "Users API",
    "version": "1.0.0",
    "x-logo": {
        "url": "https://example.com/logo.png",
        "altText": "Example Ltd"
    },
    "x-audience": "public"
}
```

The value can be anything that can be output as JSON: a string, number, bool, array, `\stdClass`, or an object that
implements `JsonSerializable`. An array with keys is output as an object, and a list as an array.

## Where extensions can go
OpenAPI allows extensions on most of its objects, and every class for one of those has an `extensions` parameter:

- the document (`OpenApi`), `Info`, `Contact`, `License`, `Server`, `ServerVariable`, `Tag` and
  `ExternalDocumentation`,
- `Components`, `PathItem`, `Operation`, `Parameter` (in both creator methods), `RequestBody`, `MediaType`,
  `Encoding`, `Response`, `Header` (in both creator methods), `Link` (in both creator methods), `Callback` and
  `NamedExample`,
- every schema, and `Xml`,
- every security scheme, `OauthFlows`, and each OAuth flow.

References can't have extensions, as OpenAPI ignores anything beside a reference. Nor can the document's paths, or an
operation's responses, which are output from a `PathCollection` and a `ResponseCollection`.

## What is checked
An extension's name must start with `x-`, as OpenAPI ignores other fields it doesn't know, and an object can't have
two extensions with the same name:

```php
$wrong = new Extension("logo", "https://example.com/logo.png"); // Throws InvalidValueException
```

Names that start with `x-oai-` or `x-oas-` are reserved for OpenAPI itself, so can't be used either.

Extensions are added after an object's own fields, so they can't replace one of them.
