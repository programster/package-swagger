# Errors and checks

- [The exceptions](#the-exceptions)
- [What is checked](#what-is-checked)
    - [When an object is created](#when-an-object-is-created)
    - [When an item is added to a collection](#when-an-item-is-added-to-a-collection)
    - [When a path or the document is created](#when-a-path-or-the-document-is-created)
- [What isn't checked](#what-isnt-checked)

## The exceptions
Every exception that the package throws extends `Programster\Swagger\Exceptions\SwaggerException`, so they can all
be caught together:

| Exception | Thrown when |
|---|---|
| `InvalidValueException` | An object is created with a value it can't accept, e.g. a URL that isn't a URL. |
| `DuplicateKeyException` | Two items with the same key go in a collection whose keys must be unique, e.g. two schemas called "User". |
| `InvalidDocumentException` | Objects that are each valid don't fit together, e.g. a path whose `{id}` has no parameter. |

Each is thrown by the constructor (or creator method) that was given the mistake, so the line in its stack trace is
the line to fix, and its message says what is wrong.

Some things are caught by PHP itself, rather than the package, as the types don't allow them, e.g. passing a
`SchemaReference` where a parameter is expected throws a `TypeError`. Adding an item of the wrong type to a
collection afterwards (e.g. `$paths->add("/users")`) throws ramsey/collection's
`Ramsey\Collection\Exception\InvalidArgumentException`. `OpenApi::toJson()` and `toYaml()` throw a `JsonException`
if the document can't be encoded, e.g. because of a string that isn't valid UTF-8.

## What is checked

### When an object is created
These throw an `InvalidValueException`:

| Class | Checks |
|---|---|
| `Info` | The title and version aren't empty, and the terms of service is a URL. |
| `Contact` | It has at least one of a name, URL or email, the URL is a URL, and the email is an email address. |
| `License`, `ExternalDocumentation`, `ExternalValueExample` | The URL is a URL. |
| `Server` | Every `{variable}` in the URL has a `ServerVariable`, and every `ServerVariable` is in the URL. |
| `ServerVariable` | Its allowed values aren't empty, are all strings, and include the default. |
| `Operation` | It has at least one response, a GET, HEAD, DELETE or TRACE operation has no request body, and its tags are strings. |
| `Path` | The path starts with a `/`. |
| `MappedResponse` | A response code given as an int is from 100 to 599. |
| `Parameter` | A path parameter is required. A header parameter isn't `Accept`, `Content-Type` or `Authorization`. `allowEmptyValue` and `allowReserved` are only used in the query. The style can be used in the parameter's location. Its content has exactly one media type. |
| `Header` | Its content has exactly one media type. |
| `MappedHeader` | The name is a valid HTTP header name. |
| `MediaType` | The mime type is a mime type. An encoding is only given for multipart or form content, and only for properties that the schema has. |
| `Encoding` | The content type is a mime type, there's no `Content-Type` header, and the style can be used in a body. |
| `RequestBody` | Its content isn't empty. |
| `Response` | It has a description, and no `Content-Type` header. |
| `SchemaString`, `SchemaArray`, `SchemaObject` | The minimum lengths and counts aren't negative, or greater than the maximums. An object's `maxProperties` isn't fewer than its required properties, and its `minProperties` isn't more than its properties when it can't have others. |
| `SchemaInt`, `SchemaNumber` | The minimum isn't greater than the maximum, an exclusive flag has its bound, and `multipleOf` is greater than 0. |
| `SchemaString`, `SchemaInt`, `SchemaNumber` | An enum isn't empty, has no value twice, has only values of the schema's type, and includes the default and example. |
| `SchemaArray`, `SchemaObject` | An array's default and example are lists, and an object's aren't. |
| `SchemaObject` | A discriminator's property is one of the object's required properties. |
| `SchemaAllOf`, `SchemaOneOf`, `SchemaAnyOf` | There's at least one schema for allOf, and at least two for oneOf and anyOf. With a discriminator, every schema is a reference, and every mapping is to one of them. |
| `Xml`, and the schemas it's given to | It has at least one option, a namespace is an absolute URI, and a prefix has a namespace. `wrapped` is only used for an array, and `attribute` isn't used for an array or an object. |
| `NamedExample` | An example that is a reference has no summary, description or extensions, as OpenAPI would ignore them. |
| `Extension` | Its name starts with `x-`, and doesn't start with the reserved `x-oai-` or `x-oas-`. |
| `NamedSchema`, `NamedParameter`, `NamedResponse`, `NamedRequestBody`, `NamedHeader`, `NamedLink`, `NamedCallback`, `NamedSecurityScheme`, `Components` | The names are valid component names (letters, numbers, dots, dashes and underscores). Example names are checked by `Components`, as examples can also be elsewhere. |
| `Reference` classes | A component's name is valid, a document's URI has no `#`, and a JSON pointer starts with a `/`. |
| The OAuth flows, `OpenIdConnect` | Every URL is a URL. |
| `OauthFlows` | It has at least one flow. |
| `RequiredScheme` | Scopes are only given for an OAuth2 or OpenIdConnect scheme, and for OAuth2, only ones that its flows offer. |

Empty names (e.g. of a property, tag or scope) aren't allowed either.

### When an item is added to a collection
These throw a `DuplicateKeyException`, whether the items are given to the constructor or added afterwards:

| Collection | Its items must have unique |
|---|---|
| `SchemaMap`, `PropertyMap`, `ExampleMap`, `ParameterMap`, `RequestBodyMap`, `ResponseMap`, `LinkMap`, `CallbackMap`, `SecuritySchemeMap`, `HeaderMap`, `HeaderCollection`, `ScopeMap`, `ServerVariableMap`, `TagCollection`, `ExtensionMap` | names |
| `DiscriminatorMappingMap` | values |
| `ContentMap` | mime types |
| `EncodingMap` | property names |
| `ResponseCollection` | response codes |
| `OperationCollection` | methods |
| `PathCollection` | paths, where paths that only differ in their variables' names are the same |
| `ParameterCollection` | names and locations together |
| `SecurityRequirement` | schemes |

### When a path or the document is created
These need more than one object, so they're checked by the object that has them all, and throw an
`InvalidDocumentException`:

| Class | Checks |
|---|---|
| `Path` | Every variable in the path has a path parameter of the same name for every operation (unless an operation has a reference to a parameter, which it could be), and every path parameter is in the path. This isn't checked when the path item is a reference. |
| `OpenApi` | Every security scheme that a security requirement uses is in the components, and no two operations have the same operation ID. |

## What isn't checked
The package can't check:

- that a reference points at something that exists, as it can be created before what it points at,
- the operations of a path item that is a reference to another document, such as their operation IDs and security,
- that a schema's `pattern` is a valid regular expression, as OpenAPI uses JavaScript's dialect, rather than PHP's,
- or that examples and defaults match their schemas.

To check these, validate the finished document with a tool such as
[swagger-cli](../README.md#validating-a-document).
