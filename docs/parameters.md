# Parameters

- [Creating a parameter](#creating-a-parameter)
- [Locations](#locations)
- [Styles](#styles)
- [Parameters whose value is JSON](#parameters-whose-value-is-json)
- [Examples](#examples)
- [Collections of parameters](#collections-of-parameters)
- [Reusing a parameter](#reusing-a-parameter)

## Creating a parameter
Most parameters are created with `Parameter::createFromSchema()`, from their name, where they go, and a schema for
their value:

```php
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaString;

$page = Parameter::createFromSchema(
    name: "page",
    in: ParameterLocation::QUERY,
    schema: new SchemaInt(minimum: 1, default: 1),
    description: "The page of results to get.",
);
```

| Parameter | Type | Description |
|---|---|---|
| `name` | `string` | The name, as it is sent in the request. |
| `in` | `ParameterLocation` | Where it is sent: `PATH`, `QUERY`, `HEADER` or `COOKIE`. |
| `schema` | `SchemaInterface` | The schema of its value, or a reference to one. See [Schemas](schemas.md). |
| `required` | `?bool` | Whether it must be sent. Defaults to true for path parameters, and false for the rest. |
| `description` | `?string` | A description. CommonMark syntax may be used. |
| `deprecated` | `bool` | Whether it is deprecated. |
| `allowEmptyValue` | `bool` | Whether it may be sent with an empty value, e.g. `?search=`. Only for the query. |
| `style` | `?Style` | How its value is serialized. See [Styles](#styles). |
| `explode` | `?bool` | For an array or object value, whether each value is sent as a separate parameter. |
| `allowReserved` | `bool` | Whether reserved characters, such as `/` and `?`, are sent without being percent-encoded. Only for the query. |
| `example` | `ValueExample\|ExampleMap\|null` | An example, or a map of named ones. See [Examples](#examples). |

## Locations
Where a parameter goes changes what it can have:

- **Path** parameters fill in a variable in the path, e.g. the `id` in `/users/{id}`, so they are always required.
  Leave `required` out for them, as it defaults to true. Setting it to false throws an `InvalidValueException`.
- **Query** parameters are the only ones that can have `allowEmptyValue` or `allowReserved`.
- **Header** parameters can't be called `Accept`, `Content-Type` or `Authorization`, as OpenAPI ignores them. The
  first two come from the content of the request and its responses, and the third from the
  [security schemes](security.md).
- **Cookie** parameters are sent in the `Cookie` header.

```php
$id = Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString(), required: false); // Throws InvalidValueException
```

## Styles
A style says how an array or object value is serialized, e.g. whether an array of IDs is sent as `?ids=1,2,3` or
`?ids=1&ids=2&ids=3`. Each style can only be used in some locations:

| Style | Locations | Example of an array value |
|---|---|---|
| `Style::MATRIX` | path | `;ids=1,2,3` |
| `Style::LABEL` | path | `.1.2.3` |
| `Style::FORM` | query, cookie | `ids=1,2,3`, or `ids=1&ids=2&ids=3` with `explode` |
| `Style::SIMPLE` | path, header | `1,2,3` |
| `Style::SPACE_DELIMITED` | query | `ids=1%202%203` |
| `Style::PIPE_DELIMITED` | query | `ids=1\|2\|3` |
| `Style::DEEP_OBJECT` | query | for objects: `filter[name]=bob&filter[age]=30` |

Each location has a default style (`simple` for the path and headers, and `form` for the query and cookies), so the
style only needs to be set for another one:

```php
use Programster\Swagger\Schemas\SchemaArray;
use Programster\Swagger\Style;

$ids = Parameter::createFromSchema(
    name: "ids",
    in: ParameterLocation::QUERY,
    schema: new SchemaArray(new SchemaInt()),
    style: Style::PIPE_DELIMITED,
);

$wrong = Parameter::createFromSchema("ids", ParameterLocation::QUERY, new SchemaString(), style: Style::MATRIX); // Throws InvalidValueException
```

## Parameters whose value is JSON
A parameter whose value is in a format such as JSON is described by a media type, rather than a schema, with
`Parameter::createFromContentMap()`. Its content must have exactly one media type:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;

$filter = Parameter::createFromContentMap(
    name: "filter",
    in: ParameterLocation::QUERY,
    content: new ContentMap(new MediaType(MimeType::JSON, new SchemaObject(properties: new PropertyMap(
        new Property("name", new SchemaString()),
    )))),
);
```

## Examples
A parameter can have one example, or a map of named examples, in which each can have a summary and description:

```php
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;

$search = Parameter::createFromSchema("q", ParameterLocation::QUERY, new SchemaString(), example: new ValueExample("bob"));

$sort = Parameter::createFromSchema(
    name: "sort",
    in: ParameterLocation::QUERY,
    schema: new SchemaString(),
    example: new ExampleMap(
        new NamedExample("byName", new ValueExample("name"), "Sort by name."),
        new NamedExample("newestFirst", new ValueExample("-created"), "Newest first."),
    ),
);
```

See [Examples](content.md#examples) for more about them.

## Collections of parameters
An operation's parameters, and a path item's, go in a `ParameterCollection`. A parameter is identified by its name
and location together, so there can be a `id` in the path and an `id` in the query, but not two `id`s in the query:

```php
use Programster\Swagger\ParameterCollection;

$parameters = new ParameterCollection(
    Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString()),
    Parameter::createFromSchema("id", ParameterLocation::QUERY, new SchemaString()),
);

$parameters->add(Parameter::createFromSchema("id", ParameterLocation::QUERY, new SchemaInt())); // Throws DuplicateKeyException
```

## Reusing a parameter
A parameter that many operations take can be defined once, in the components, and referenced from each:

```php
use Programster\Swagger\Components;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\ParameterMap;

$pageParameter = new NamedParameter("page", $page);
$components = new Components(parameters: new ParameterMap($pageParameter));

// The reference can go in any ParameterCollection, e.g. an operation's.
$listParameters = new ParameterCollection($pageParameter->createReference());
```

See [Components and references](components-and-references.md) for more.
