# Getting started

- [How a document fits together](#how-a-document-fits-together)
- [Building a document](#building-a-document)
    - [The information about the API](#the-information-about-the-api)
    - [An operation and its responses](#an-operation-and-its-responses)
    - [Paths](#paths)
    - [The document](#the-document)
- [Outputting the document](#outputting-the-document)
- [Collections](#collections)
- [When something is wrong](#when-something-is-wrong)

## How a document fits together
An OpenAPI document describes an API: its paths (e.g. `/users/{id}`), the operations on each path (e.g. GET and
DELETE), what each operation takes (parameters and a request body), and what it can return (responses). The package
has a class for each part, and they fit together like this:

```text
OpenApi
├── Info                        the API's title, version, contact, license
├── ServerCollection            where the API is (see servers.md)
├── PathCollection
│   └── Path                    e.g. "/users/{id}"
│       └── PathItem
│           ├── ParameterCollection         parameters shared by its operations
│           └── OperationCollection
│               └── Operation               e.g. GET
│                   ├── ParameterCollection
│                   │   └── Parameter       e.g. the "id" in the path
│                   ├── RequestBody
│                   │   └── ContentMap
│                   │       └── MediaType   e.g. JSON, with its schema and examples
│                   └── ResponseCollection
│                       └── MappedResponse  a response code, e.g. 200, and its...
│                           └── Response    description, content, headers and links
├── Components                  things defined once and referenced elsewhere, and the security schemes
├── SecurityRequirementCollection
└── TagCollection
```

Every class is in the `Programster\Swagger` namespace, or one of these within it:

| Namespace | What's in it |
|---|---|
| `Schemas` | the schemas (`SchemaString`, `SchemaObject`, etc.), and their formats |
| `Examples` | examples, for content, parameters and headers |
| `Security` | security schemes (`ApiKey`, `OAuth2`, etc.), and security requirements |
| `OAuth` | the OAuth flows, and their scopes |
| `References` | references to things defined elsewhere, e.g. `SchemaReference` |
| `Exceptions` | the exceptions the package throws |

## Building a document
A document is built from the bottom up, as each object is given everything it needs when it is created. These
examples build a small API for getting a user.

### The information about the API
Every document needs a title and a version, which is the version of your API (not of OpenAPI):

```php
use Programster\Swagger\Contact;
use Programster\Swagger\Info;
use Programster\Swagger\License;

$info = new Info(
    title: "Users API",
    version: "1.2.0",
    description: "An API for managing users.",
    contact: new Contact(name: "Support", email: "support@example.com"),
    license: new License("MIT", "https://opensource.org/licenses/MIT"),
);
```

### An operation and its responses
An operation needs its HTTP method and at least one response. A response needs a description, and has content if
it has a body:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\Operation;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;

$userSchema = new SchemaObject(
    description: "A user.",
    properties: new PropertyMap(
        new Property("id", new SchemaString(format: StringFormat::UUID), required: true),
        new Property("name", new SchemaString(maxLength: 255), required: true),
        new Property("email", new SchemaString(format: StringFormat::EMAIL)),
    ),
);

$found = new Response(
    "The user.",
    new ContentMap(new MediaType(MimeType::JSON, $userSchema))
);

$notFound = new Response("There is no user with that ID.");

$getUser = new Operation(
    method: Method::GET,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::OK, $found),
        new MappedResponse(ResponseCode::NOT_FOUND, $notFound),
    ),
    summary: "Get a user.",
    parameters: new ParameterCollection(
        Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString(format: StringFormat::UUID))
    ),
    operationId: "getUser",
);
```

The schema is written out in full here. To define it once and use it in several places, put it in the components
and reference it, as [Components and references](components-and-references.md) explains.

### Paths
A path is made from its relative path and a `PathItem`, which holds the operations on it (one for each method):

```php
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;

$paths = new PathCollection(
    new Path(
        "/users/{id}",
        new PathItem(new OperationCollection($getUser))
    ),
);
```

The `{id}` in the path must have a path parameter called `id`, which `$getUser` has. If it didn't, creating the
`Path` would throw an `InvalidDocumentException`, saying so.

### The document
The document puts it all together:

```php
use Programster\Swagger\OpenApi;

$document = new OpenApi(info: $info, paths: $paths);
```

It can also be given the servers that the API is on (see [Servers](servers.md)), the components (see
[Components and references](components-and-references.md)), the security that the operations need (see
[Security](security.md)), descriptions of the tags that operations are grouped by, and a link to external
documentation.

## Outputting the document
The document can be output as JSON or YAML:

```php
$json = $document->toJson();
$yaml = $document->toYaml();

// Casting the document to a string gives the same JSON as toJson().
$sameJson = (string)$document;
```

`toJson()` pretty-prints the JSON, and doesn't escape slashes. `toYaml()` needs the `yaml` extension. Both throw a
`JsonException` if the document can't be encoded, e.g. because an example has a string that isn't valid UTF-8.

To serve the document, so that a tool such as Swagger UI can load it, return the JSON with the
`application/json` content type, e.g. with `header("Content-Type: application/json"); echo $document;`. To save it
instead, write it to a file with `file_put_contents("openapi.json", $document->toJson())`.

Every object is also `JsonSerializable`, so any part of the document can be passed to `json_encode()` on its own,
which is handy for checking what part of it looks like.

## Collections
The collections (e.g. `PathCollection`, `ParameterCollection`) are [ramsey/collection](https://github.com/ramsey/collection)
collections. They are given their items in their constructor, but can also be added to afterwards, and have the
usual collection methods, such as `count()`, `filter()` and iterating with `foreach`:

```php
$paths->add(new Path("/users", new PathItem()));
$numPaths = count($paths);
```

The objects that are given a collection take a copy of it, so changing the collection afterwards doesn't change
them. In the example above, the `/users` path isn't in `$document`, as it was added after the document was created.

The collections that are maps (e.g. `SchemaMap`, `HeaderCollection`) output their items under their names, and two
items with the same name can't be put in one, whether in the constructor or afterwards:

```php
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\Schemas\SchemaInt;

$rateLimit = new MappedHeader(
    "X-Rate-Limit",
    Header::createFromSchema(new SchemaInt())
);

$headers = new HeaderCollection($rateLimit, $rateLimit); // Throws DuplicateKeyException
```

## When something is wrong
Each object checks the values it is given, and throws an `InvalidValueException` if one can't be used, saying what
is wrong. For example, an email address that isn't one:

```php
$contact = new Contact(email: "support"); // Throws InvalidValueException
```

All the package's exceptions extend `SwaggerException`. See [Errors and checks](errors.md) for what each one means,
and everything that is checked.
