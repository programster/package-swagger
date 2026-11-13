# Request bodies, content and examples

- [Content](#content)
    - [Mime types](#mime-types)
- [Request bodies](#request-bodies)
- [Examples](#examples)
    - [One example](#one-example)
    - [Several named examples](#several-named-examples)
- [Encoding multipart and form bodies](#encoding-multipart-and-form-bodies)

## Content
The content of a request body, a response, or a parameter or header whose value is JSON, is a `ContentMap` of
`MediaType`s: one for each mime type it can be sent as, with the schema of what it looks like in that format:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;

$user = new SchemaObject(properties: new PropertyMap(
    new Property("name", new SchemaString(), required: true),
));

$content = new ContentMap(
    new MediaType(MimeType::JSON, $user),
    new MediaType(MimeType::XML, $user),
);
```

A `ContentMap` needs at least one media type, and can only have one for each mime type.

A media type's schema is optional, for content that has no structure to describe, such as a file download:

```php
$download = new ContentMap(new MediaType(MimeType::PDF));
```

### Mime types
`MimeType` has the common mime types, such as `JSON`, `XML`, `FORM_URL_ENCODED`, `MULTIPART_FORM_DATA`,
`PLAIN_TEXT`, `HTML`, `CSV`, `PDF`, `PNG` and `BINARY`, as well as `IMAGE` (`image/*`) and `ANY` (`*/*`). Anything
that takes a `MimeType` also takes a string, for one that the enum doesn't have. The string is checked, so it must
look like a mime type, but may have parameters:

```php
$jsonApi = new MediaType("application/vnd.api+json");
$utf8Text = new MediaType("text/plain; charset=utf-8");
$typo = new MediaType("json"); // Throws InvalidValueException
```

## Request bodies
A request body has its content, and can say whether it is required, and have a description:

```php
use Programster\Swagger\RequestBody;

$createUser = new RequestBody(
    content: new ContentMap(new MediaType(MimeType::JSON, $user)),
    description: "The user to create.",
    required: true,
);
```

A request body that several operations take can be defined once in the components, and referenced, as
[Components and references](components-and-references.md) explains. Remember that GET, HEAD, DELETE and TRACE
operations can't have a request body.

## Examples
Examples show what the content (or a parameter or header) looks like. Tools such as Swagger UI show them, and use
them to fill in requests that are tried out.

### One example
A `ValueExample` holds the example itself, as a string, number, boolean or array:

```php
use Programster\Swagger\Examples\ValueExample;

$withExample = new MediaType(MimeType::JSON, $user, new ValueExample(['name' => "Bob"]));
```

### Several named examples
An `ExampleMap` holds several examples, each with a name, and optionally a summary and description:

```php
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\NamedExample;

$examples = new ExampleMap(
    new NamedExample(
        name: "short",
        example: new ValueExample(['name' => "Bo"]),
        summary: "A short name.",
    ),
    new NamedExample(
        name: "long",
        example: new ValueExample(['name' => "Bartholomew"]),
        summary: "A long name.",
        description: "Names can be up to 255 characters.",
    ),
    new NamedExample(
        name: "fromFile",
        example: new ExternalValueExample("https://example.com/examples/user.json"),
    ),
);

$withExamples = new MediaType(MimeType::JSON, $user, $examples);
```

An `ExternalValueExample` is an example at a URL, for one that is too big to put in the document, or can't be
written as JSON (e.g. an image). OpenAPI only allows these in a map of named examples, so a media type, parameter or
header can't be given one on its own.

An example that is used in several places can be defined once in the components, and referenced from each. See
[Referencing an example](components-and-references.md#referencing-an-example).

## Encoding multipart and form bodies
A request body that is `multipart/form-data` or `application/x-www-form-urlencoded` (e.g. a form with a file upload)
can say how each of its properties is encoded, with an `Encoding` for each:

```php
use Programster\Swagger\Encoding;
use Programster\Swagger\EncodingMap;
use Programster\Swagger\Schemas\StringFormat;

$profile = new SchemaObject(properties: new PropertyMap(
    new Property("name", new SchemaString()),
    new Property("photo", new SchemaString(format: StringFormat::BINARY)),
));

$upload = new RequestBody(
    new ContentMap(
        new MediaType(
            mimeType: MimeType::MULTIPART_FORM_DATA,
            schema: $profile,
            encoding: new EncodingMap(new Encoding("photo", contentType: MimeType::PNG)),
        )
    )
);
```

| Parameter | Type | Description |
|---|---|---|
| `propertyName` | `string` | The property of the schema that it is for. |
| `contentType` | `MimeType\|string\|null` | The mime type the property is sent as, e.g. an image in a multipart body. |
| `headers` | `HeaderCollection` | Extra headers sent with the property in a multipart body, e.g. `Content-Disposition`. `Content-Type` can't be one, as it is set by `contentType`. |
| `style` | `?Style` | How the property is serialized in a form body: `FORM`, `SPACE_DELIMITED`, `PIPE_DELIMITED` or `DEEP_OBJECT`. |
| `explode` | `?bool` | For an array or object value, whether each value is sent separately. |
| `allowReserved` | `bool` | Whether reserved characters are sent without being percent-encoded. |

An encoding can only be given for multipart or form content, and only for a property that the schema has:

```php
$photoEncoding = new EncodingMap(new Encoding("photo"));
$wrong = new MediaType(MimeType::JSON, $profile, encoding: $photoEncoding); // Throws InvalidValueException
```
