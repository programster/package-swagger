# Paths and operations

- [Paths](#paths)
    - [Path variables](#path-variables)
    - [What a path item can have](#what-a-path-item-can-have)
- [Operations](#operations)
    - [Request bodies](#request-bodies)
    - [Operation IDs](#operation-ids)
    - [Tags](#tags)
    - [Deprecating an operation](#deprecating-an-operation)
- [Callbacks](#callbacks)

## Paths
A `Path` is made from its relative path, which is relative to the server's URL and must start with a `/`, and a
`PathItem`, which holds the operations on the path. The document's paths go in a `PathCollection`:

```php
use Programster\Swagger\MappedResponse;
use Programster\Swagger\Method;
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

$users = new Path(
    "/users",
    new PathItem(new OperationCollection(
        new Operation(Method::GET, $ok, summary: "List the users."),
        new Operation(Method::POST, $ok, summary: "Create a user."),
    ))
);

$paths = new PathCollection($users);
```

An `OperationCollection` can only have one operation for each method, and a `PathCollection` can only have one
`Path` for each path. Two paths that only differ in the names of their variables, such as `/users/{id}` and
`/users/{name}`, are the same path to OpenAPI, so they can't both be in a `PathCollection`.

### Path variables
A variable in a path, such as the `id` in `/users/{id}`, is filled in by a path parameter of the same name. Every
operation on the path must have one, either in its own parameters or in the path item's, which every operation on
the path shares:

```php
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Schemas\SchemaString;

$id = Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString());

$user = new Path(
    "/users/{id}",
    new PathItem(
        operations: new OperationCollection(
            new Operation(Method::GET, $ok, summary: "Get a user."),
            new Operation(Method::DELETE, $ok, summary: "Delete a user."),
        ),
        parameters: new ParameterCollection($id),
    )
);
```

The `Path` checks this, and throws an `InvalidDocumentException` if a variable has no parameter, or a path parameter
isn't in the path:

```php
$getGroup = new Operation(Method::GET, $ok);

$broken = new Path(
    "/groups/{id}",
    new PathItem(new OperationCollection($getGroup))
); // Throws InvalidDocumentException
```

A parameter can also be a reference to one in the components (a `ParameterReference`). What a reference points at
isn't known until the document is read, so when an operation has one, a variable without a parameter isn't treated
as a mistake.

### What a path item can have

| Parameter | Type | Description |
|---|---|---|
| `operations` | `OperationCollection` | The operations on the path, one per method. |
| `summary` | `?string` | A short summary of what the operations are for. |
| `description` | `?string` | A longer description. CommonMark syntax may be used. |
| `servers` | `ServerCollection` | Servers for these operations, instead of the document's. See [Servers](servers.md). |
| `parameters` | `ParameterCollection` | Parameters that every operation on the path takes. An operation can override one with its own parameter of the same name and location. |
| `extensions` | `ExtensionMap` | [Specification extensions](extensions.md). |

A path can also be a reference to a path item in another document, with a `PathItemReference`. See
[Referencing another document](components-and-references.md#referencing-another-document).

## Operations
An `Operation` needs its method and a `ResponseCollection` with at least one response. Everything else is optional:

| Parameter | Type | Description |
|---|---|---|
| `method` | `Method` | The HTTP method, e.g. `Method::GET`. |
| `responses` | `ResponseCollection` | The responses it can give, under their response codes. See [Responses](responses.md). |
| `summary` | `?string` | A short summary of what it does. |
| `description` | `?string` | A longer description. CommonMark syntax may be used. |
| `parameters` | `ParameterCollection` | Its parameters, as well as the path item's. See [Parameters](parameters.md). |
| `requestBody` | `?RequestBodyInterface` | Its request body, or a reference to one. See [Request bodies](content.md). |
| `tags` | `string[]` | The names of the tags it is grouped under. |
| `operationId` | `?string` | A unique ID, e.g. `getUser`, which links and code generators use. |
| `externalDocs` | `?ExternalDocumentation` | A link to documentation elsewhere. |
| `callbacks` | `CallbackMap` | Requests the API may make back to the client. See [Callbacks](#callbacks). |
| `deprecated` | `bool` | Whether it is deprecated. |
| `security` | `?SecurityRequirementCollection` | Its own security, instead of the document's. See [Security](security.md). |
| `servers` | `ServerCollection` | Servers for it, instead of the path's or document's. |

### Request bodies
OpenAPI only allows a request body for the methods where HTTP says what one means, so a GET, HEAD, DELETE or TRACE
operation can't have one:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\RequestBody;

$body = new RequestBody(
    new ContentMap(new MediaType(MimeType::JSON))
);

$createUser = new Operation(Method::POST, $ok, requestBody: $body);
$search = new Operation(Method::GET, $ok, requestBody: $body); // Throws InvalidValueException
```

To filter a GET request, use query parameters instead.

### Operation IDs
An operation ID identifies an operation, e.g. for a [link](responses.md#links) to point at it, or for the name of
the method that a code generator creates for it. Each must be unique within the document, which the `OpenApi`
checks when it is created, throwing an `InvalidDocumentException` if two operations have the same one.

### Tags
Tags group operations, e.g. in Swagger UI, which shows each tag as a section. An operation is tagged with the names of
its tags. The tags only need to be described in the document if they should have a description, or a link to
external documentation:

```php
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Info;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Tag;
use Programster\Swagger\TagCollection;

$listUsers = new Operation(Method::GET, $ok, tags: ["users"]);

$document = new OpenApi(
    info: new Info("Users API", "1.0"),
    paths: new PathCollection(
        new Path(
            "/users",
            new PathItem(new OperationCollection($listUsers))
        )
    ),
    tags: new TagCollection(
        new Tag("users", "Managing users.", new ExternalDocumentation("https://docs.example.com/users")),
    ),
);
```

### Deprecating an operation
Set `deprecated: true` for an operation that still works, but shouldn't be used any more. Parameters and headers can
be deprecated in the same way.

## Callbacks
A callback describes a request that the API makes back to the client, e.g. a webhook to a URL that the client gave.
It is made from a runtime expression for the URL, and a `PathItem` with the operations that may be called there:

```php
use Programster\Swagger\Callback;
use Programster\Swagger\CallbackMap;
use Programster\Swagger\NamedCallback;

$onUserCreated = new Callback(
    '{$request.body#/callbackUrl}',
    new PathItem(
        new OperationCollection(new Operation(Method::POST, $ok, summary: "A user was created."))
    )
);

$subscribe = new Operation(
    Method::POST,
    $ok,
    summary: "Subscribe to new users.",
    callbacks: new CallbackMap(new NamedCallback("userCreated", $onUserCreated)),
);
```

The expression is in single quotes, so that PHP doesn't treat `{$request...}` as a variable. A callback can also be
defined once in the components, and referenced from each operation, as
[Components and references](components-and-references.md) explains.
