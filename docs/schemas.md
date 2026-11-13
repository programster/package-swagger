# Schemas

- [The kinds of schema](#the-kinds-of-schema)
- [Options that every schema has](#options-that-every-schema-has)
- [Strings](#strings)
- [Integers and numbers](#integers-and-numbers)
- [Limiting a value to some values (enum)](#limiting-a-value-to-some-values-enum)
- [Booleans](#booleans)
- [Arrays](#arrays)
- [Objects](#objects)
- [Read-only and write-only properties](#read-only-and-write-only-properties)
- [Maps (additionalProperties)](#maps-additionalproperties)
- [Any value](#any-value)
- [Null values](#null-values)
- [Combining schemas](#combining-schemas)
- [Choosing a schema with a discriminator](#choosing-a-schema-with-a-discriminator)
- [XML](#xml)
- [Reusing a schema](#reusing-a-schema)

## The kinds of schema
A schema describes a value, such as the body of a request, or a parameter. There is a class for each type of value,
and for each way of combining schemas, in the `Programster\Swagger\Schemas` namespace. They all implement
`SchemaInterface`, as does a `SchemaReference`, so a reference to a schema can be used anywhere a schema can.

| Class | For | Options |
|---|---|---|
| `SchemaString` | strings | length, pattern, format, enum |
| `SchemaInt` | integers | range, multipleOf, format, enum |
| `SchemaNumber` | numbers, which may have a fractional part | range, multipleOf, format, enum |
| `SchemaBool` | booleans | |
| `SchemaArray` | arrays, whose items all match a schema | number of items, uniqueness |
| `SchemaObject` | objects with named properties, and maps | the properties, other properties, how many there can be |
| `SchemaAnyValue` | a value of any type | |
| `SchemaAllOf` | a value that matches all of several schemas | a discriminator, on the schema being extended |
| `SchemaOneOf` | a value that matches exactly one of several schemas | a discriminator |
| `SchemaAnyOf` | a value that matches at least one of several schemas | a discriminator |
| `SchemaNot` | a value that doesn't match a schema | |

## Options that every schema has
Every schema can have:

- a `description`, in which CommonMark syntax may be used,
- a `title`,
- an `example` of the value (for a string schema, a string, and so on),
- `deprecated: true`, if the value shouldn't be used any more,
- `externalDocs`, an `ExternalDocumentation` with a link to documentation elsewhere,
- and `extensions`, for [specification extensions](extensions.md).

The schemas for a type of value (strings, integers, numbers, booleans, arrays and objects) can also be
[`nullable`](#null-values), have a `default` (the value to assume when none is given), and have [`xml`](#xml)
options.

## Strings

```php
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;

$username = new SchemaString(
    description: "The user's username.",
    minLength: 3,
    maxLength: 32,
    pattern: "^[a-z0-9_]+$",
    example: "bob_smith",
);

$email = new SchemaString(format: StringFormat::EMAIL);
```

The `pattern` is a regular expression in the ECMA 262 dialect (the one JavaScript uses), as OpenAPI requires. The
lengths can't be negative, and the minimum can't be more than the maximum:

```php
$impossible = new SchemaString(minLength: 10, maxLength: 5); // Throws InvalidValueException
```

The `format` says more about what the string holds, which tools can use to check it or show a better input. OpenAPI
defines `DATE`, `DATE_TIME`, `PASSWORD`, `BYTE` (base64 encoded data) and `BINARY` (e.g. the contents of an uploaded
file), and `StringFormat` also has the formats from JSON Schema, such as `EMAIL`, `UUID`, `URI`, `HOSTNAME` and
`IPV4`.

## Integers and numbers
`SchemaInt` is for whole numbers, and `SchemaNumber` for numbers that may have a fractional part. They take the same
options:

```php
use Programster\Swagger\Schemas\IntegerFormat;
use Programster\Swagger\Schemas\NumberFormat;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaNumber;

$age = new SchemaInt(minimum: 0, maximum: 150, format: IntegerFormat::INT32);

// A price that is more than 0, in steps of 0.01.
$price = new SchemaNumber(minimum: 0, exclusiveMinimum: true, multipleOf: 0.01, format: NumberFormat::DOUBLE);
```

In OpenAPI 3.0, `exclusiveMinimum` and `exclusiveMaximum` are flags: they make the minimum or maximum exclusive, so
the value must be greater than the minimum (or less than the maximum), rather than being allowed to equal it. That
means each needs its bound, and `multipleOf` must be greater than 0:

```php
$noMinimum = new SchemaInt(exclusiveMinimum: true); // Throws InvalidValueException
```

The formats are `IntegerFormat::INT32` and `INT64`, and `NumberFormat::FLOAT` and `DOUBLE`.

## Limiting a value to some values (enum)
A string, integer or number schema can be limited to a list of values with `enum`. For a string-backed PHP enum, the
list can be made from its cases, so it can't fall out of date with them:

```php
use Programster\Swagger\Method;

$status = new SchemaString(enum: ["active", "suspended", "deleted"], default: "active");
$priority = new SchemaInt(enum: [1, 2, 3]);

// Any string-backed PHP enum's values, e.g. this package's Method enum.
$method = new SchemaString(enum: array_column(Method::cases(), 'value'));
```

The list can't be empty, or have the same value twice, and each value must be of the schema's type. A default or
example must be one of the values:

```php
$wrongDefault = new SchemaString(enum: ["active", "suspended"], default: "deleted"); // Throws InvalidValueException
```

## Booleans

```php
use Programster\Swagger\Schemas\SchemaBool;

$active = new SchemaBool(description: "Whether the user can log in.", default: true);
```

## Arrays
An array's schema needs the schema that every item matches, as OpenAPI requires it:

```php
use Programster\Swagger\Schemas\SchemaArray;

$tags = new SchemaArray(
    items: new SchemaString(maxLength: 20),
    minItems: 1,
    maxItems: 10,
    uniqueItems: true,
    example: ["admin", "beta"],
);
```

An array's `default` and `example` must be lists, as they are output as JSON arrays.

## Objects
An object's properties are each a `Property`, with its name, its schema, and whether it is required. They go in a
`PropertyMap`, so two can't have the same name:

```php
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;

$user = new SchemaObject(
    description: "A user.",
    properties: new PropertyMap(
        new Property("id", new SchemaString(format: StringFormat::UUID), required: true),
        new Property("username", $username, required: true),
        new Property("age", $age),
        new Property("tags", $tags),
    ),
    example: ['id' => "e0c3b8a4-6f0d-4b8e-9c1a-2f4d5e6a7b8c", 'username' => "bob_smith"],
);
```

Each property says whether it is required, so a required property can't be misspelt, or left in the list of
required properties after it is removed, as can happen when they are listed separately. The JSON still lists them
separately, as OpenAPI does:

```json
{
    "type": "object",
    "description": "A user.",
    "properties": {
        "id": {"type": "string", "format": "uuid"},
        "...": "..."
    },
    "required": ["id", "username"]
}
```

`minProperties` and `maxProperties` limit how many properties an object can have. `maxProperties` can't be fewer
than the number of required properties, as no object could then be valid.

An object's `default` and `example` are arrays keyed by property name, as they are output as JSON objects, so they
can't be lists.

## Read-only and write-only properties
A property that is only in responses, such as an ID that the server sets, is read-only, and one that is only in
requests, such as a password, is write-only. This is set on the `Property` with an `Access`, as it only has an effect
on an object's properties:

```php
use Programster\Swagger\Schemas\Access;

$account = new SchemaObject(properties: new PropertyMap(
    new Property(
        "id",
        new SchemaString(),
        required: true,
        access: Access::READ_ONLY
    ),
    new Property(
        "password",
        new SchemaString(format: StringFormat::PASSWORD),
        required: true,
        access: Access::WRITE_ONLY
    ),
));
```

A read-only property that is required is only required in responses, and a write-only one only in requests, so the
same schema can be used for both.

OpenAPI ignores anything beside a reference, so when a property's schema is a reference, it is wrapped in an `allOf`
to mark it, e.g. `{"allOf": [{"$ref": "#/components/schemas/Id"}], "readOnly": true}`.

## Maps (additionalProperties)
An object can have properties other than the ones it lists, unless it says otherwise. `additionalProperties` says
whether it can: `true` (the default) allows any, `false` allows none, and a schema allows any whose value matches it.
A schema with only `additionalProperties` describes a map (or dictionary), whose keys can be anything:

```php
// A map of names to scores, e.g. {"alice": 10, "bob": 7}.
$scores = new SchemaObject(additionalProperties: new SchemaInt(minimum: 0));

// An object that can only have the properties it lists.
$point = new SchemaObject(
    properties: new PropertyMap(
        new Property("x", new SchemaNumber()),
        new Property("y", new SchemaNumber()),
    ),
    additionalProperties: false,
);
```

## Any value
`SchemaAnyValue` is a schema that any value matches, whatever its type, e.g. for some metadata that is up to the
client. It is output as `{}`, plus any description:

```php
use Programster\Swagger\Schemas\SchemaAnyValue;

$metadata = new SchemaAnyValue(description: "Anything the client wants to store.");
```

## Null values
A schema with `nullable: true` also allows `null`:

```php
$middleName = new SchemaString(nullable: true);
```

This is different from a property not being required. A property that isn't required can be left out of the object,
while a nullable one can be there with a value of `null`.

When a nullable schema has an `enum`, `null` is added to the enum in the output, as OpenAPI only allows the values in
the enum.

## Combining schemas
Schemas can be combined to describe a value that matches several of them, or one of several. The schemas go in a
`SchemaCollection`:

```php
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\SchemaAllOf;
use Programster\Swagger\Schemas\SchemaAnyOf;
use Programster\Swagger\Schemas\SchemaCollection;
use Programster\Swagger\Schemas\SchemaNot;
use Programster\Swagger\Schemas\SchemaOneOf;

$namedUser = new NamedSchema("User", $user);

// allOf: an admin is a user, with some extra properties.
$admin = new SchemaAllOf(new SchemaCollection(
    $namedUser->createReference(),
    new SchemaObject(properties: new PropertyMap(
        new Property("permissions", $tags, required: true),
    )),
));

// oneOf: an ID is either a number or a UUID, but not both.
$id = new SchemaOneOf(new SchemaCollection(
    new SchemaInt(minimum: 1),
    new SchemaString(format: StringFormat::UUID),
));

// anyOf: a contact has an email address, a phone number, or both.
$contact = new SchemaAnyOf(new SchemaCollection(
    new SchemaObject(properties: new PropertyMap(
        new Property("email", $email, required: true),
    )),
    new SchemaObject(properties: new PropertyMap(
        new Property("phone", new SchemaString(), required: true),
    )),
));

// not: any string except "admin".
$notAdmin = new SchemaNot(new SchemaString(enum: ["admin"]));
```

`SchemaAllOf` needs at least one schema. With just one, it can be used to describe a reference differently where it's
used, as OpenAPI ignores anything beside a reference. `SchemaOneOf` and `SchemaAnyOf` need at least two, as with one
they would be the same as that schema:

```php
$justOne = new SchemaCollection(new SchemaString());
$pointless = new SchemaOneOf($justOne); // Throws InvalidValueException
```

## Choosing a schema with a discriminator
When a value can match one of several schemas, a `Discriminator` tells tools which one it matches, from the value of
one of its properties. Each schema must be a reference, as OpenAPI only considers referenced schemas, and each must
have the property, as a required property:

```php
use Programster\Swagger\Schemas\Discriminator;
use Programster\Swagger\Schemas\DiscriminatorMapping;
use Programster\Swagger\Schemas\DiscriminatorMappingMap;

$cat = new NamedSchema(
    "Cat",
    new SchemaObject(properties: new PropertyMap(
        new Property("petType", new SchemaString(), required: true),
        new Property("livesLeft", new SchemaInt()),
    ))
);

$dog = new NamedSchema(
    "Dog",
    new SchemaObject(properties: new PropertyMap(
        new Property("petType", new SchemaString(), required: true),
        new Property("goodBoy", new SchemaBool()),
    ))
);

$pet = new SchemaOneOf(
    schemas: new SchemaCollection($cat->createReference(), $dog->createReference()),
    discriminator: new Discriminator("petType", new DiscriminatorMappingMap(
        new DiscriminatorMapping("cat", $cat->createReference()),
        new DiscriminatorMapping("dog", $dog->createReference()),
    )),
);
```

Without a mapping for a value, the value is taken to be the name of the schema in the components (e.g. `Cat`), so
the mappings are only needed when the values are different from the names. A mapping can only be to one of the
schemas, and a schema can't be inline:

```php
$inline = new SchemaOneOf(
    schemas: new SchemaCollection($cat->createReference(), new SchemaObject()),
    discriminator: new Discriminator("petType"),
); // Throws InvalidValueException
```

A discriminator can also be given to a `SchemaObject` that other schemas extend with `SchemaAllOf`, in which case its
property must be one of the object's required properties.

## XML
For an API that sends XML, an `Xml` says how a schema's value is represented, e.g. under another element name, as an
attribute, or (for an array) wrapped in an element:

```php
use Programster\Swagger\Schemas\Xml;

$idAttribute = new SchemaInt(xml: new Xml(attribute: true));

$book = new SchemaObject(
    properties: new PropertyMap(
        new Property("id", $idAttribute),
        new Property("title", new SchemaString()),
    ),
    xml: new Xml(name: "book", namespace: "https://example.com/schema", prefix: "ex"),
);

// <books><book/><book/></books>, rather than a <book> for each item with nothing around them.
$books = new SchemaArray(items: $book, xml: new Xml(name: "books", wrapped: true));
```

`attribute` can't be used for an array or an object, and `wrapped` can only be used for an array. A prefix needs a
namespace, which must be an absolute URI.

## Reusing a schema
A schema that is used in more than one place (e.g. a user, which several operations return) can be defined once, in
the components, under a name, and referenced from wherever it's needed:

```php
use Programster\Swagger\Components;
use Programster\Swagger\Schemas\SchemaMap;

$components = new Components(schemas: new SchemaMap($namedUser, $cat, $dog));

// A reference to "#/components/schemas/User", which can be used anywhere a schema can.
$users = new SchemaArray(items: $namedUser->createReference());
```

See [Components and references](components-and-references.md) for more, including referencing schemas in other
documents.
