# Responses, headers and links

- [Responses](#responses)
- [Response codes](#response-codes)
- [Headers](#headers)
- [Links](#links)
- [Reusing a response](#reusing-a-response)
- [Reusing a header](#reusing-a-header)

## Responses
A response needs a description, which OpenAPI requires. It has content if it has a body, and can have headers and
links:

```php
use Programster\Swagger\ContentMap;
use Programster\Swagger\MediaType;
use Programster\Swagger\MimeType;
use Programster\Swagger\Response;
use Programster\Swagger\Schemas\SchemaString;

$created = new Response(
    description: "The user was created.",
    content: new ContentMap(new MediaType(MimeType::JSON, new SchemaString())),
);

// A response without a body, e.g. for a 204, has no content.
$deleted = new Response("The user was deleted.");
```

## Response codes
An operation's responses go in a `ResponseCollection`, each with its response code. There must be at least one, and
only one for each code:

```php
use Programster\Swagger\MappedResponse;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;

$responses = new ResponseCollection(
    new MappedResponse(ResponseCode::CREATED, $created),
    new MappedResponse(ResponseCode::BAD_REQUEST, new Response("The user is invalid.")),
    new MappedResponse(ResponseCode::DEFAULT, new Response("Something went wrong.")),
);
```

`ResponseCode` has the standard HTTP response codes, such as `OK` (200), `NO_CONTENT` (204) and `NOT_FOUND` (404).
Each case has a docblock saying what the code means. It also has:

- `DEFAULT`, which covers any code that the operation doesn't have its own response for,
- and a case for each range of codes, which covers any code in that range that the operation doesn't have its own
  response for: `ANY_INFORMATIONAL` (1XX), `ANY_SUCCESS` (2XX), `ANY_REDIRECTION` (3XX), `ANY_CLIENT_ERROR` (4XX)
  and `ANY_SERVER_ERROR` (5XX).

A code that `ResponseCode` doesn't have, such as one that only a particular server uses, can be given as an int.
It must be from 100 to 599:

```php
$withOtherCodes = new ResponseCollection(
    new MappedResponse(ResponseCode::OK, $created),
    new MappedResponse(ResponseCode::ANY_CLIENT_ERROR, new Response("The request was wrong.")),
    new MappedResponse(499, new Response("The client closed the connection.")),
);
```

## Headers
A response's headers go in a `HeaderCollection`, each under its name with a `MappedHeader`. Most headers are created
with `Header::createFromSchema()`, from a schema for their value:

```php
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\Schemas\SchemaInt;

$remaining = Header::createFromSchema(new SchemaInt(), required: true, description: "Requests left.");
$reset = Header::createFromSchema(new SchemaInt(), description: "When the limit resets.");

$rateLimited = new Response(
    description: "A page of users.",
    headers: new HeaderCollection(
        new MappedHeader("X-Rate-Limit-Remaining", $remaining),
        new MappedHeader("X-Rate-Limit-Reset", $reset),
    ),
);
```

A header doesn't have a name of its own, as the same header can be used under different names, and a header in the
components is under its component name instead (see [Reusing a header](#reusing-a-header)).

A header whose value is JSON can be created with `Header::createFromContentMap()`, with a `ContentMap` that has
exactly one media type. A header can also be deprecated, and have examples, in the same way as a
[parameter](parameters.md).

A response can't have a `Content-Type` header, as OpenAPI ignores it: the response's content says what the content
types are.

```php
$contentType = new MappedHeader("Content-Type", Header::createFromSchema(new SchemaString()));
$wrong = new Response("A user.", headers: new HeaderCollection($contentType)); // Throws InvalidValueException
```

## Links
A link says how a value from a response can be used to call another operation, e.g. that the ID in a "create user"
response can be used to get the user. It points at the operation by its operation ID, or by a reference to it, so
there's a creator method for each:

```php
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\NamedLink;

$getUser = Link::createFromOperationId(
    operationId: "getUser",
    parameters: ['id' => '$response.body#/id'],
    description: "Get the user that was created.",
);

$createdWithLink = new Response(
    description: "The user was created.",
    content: new ContentMap(new MediaType(MimeType::JSON, new SchemaString())),
    links: new LinkMap(new NamedLink("GetUser", $getUser)),
);

// An operation in another document can be pointed at by a reference to it.
$elsewhere = Link::createFromOperationRef("https://example.com/openapi.json#/paths/~1groups~1{id}/get");
```

The values of the parameters are constants, or [runtime expressions](https://swagger.io/specification/v3/#runtime-expressions)
such as `$response.body#/id`. They are in single quotes, so that PHP doesn't treat them as variables. A link can also
have a request body to send, and a server to call the operation on.

## Reusing a response
A response that many operations give (e.g. a "not found" error) can be defined once in the components, and
referenced from each:

```php
use Programster\Swagger\Components;
use Programster\Swagger\NamedResponse;
use Programster\Swagger\ResponseMap;

$notFound = new NamedResponse("NotFound", new Response("There is nothing with that ID."));
$components = new Components(responses: new ResponseMap($notFound));

$getResponses = new ResponseCollection(
    new MappedResponse(ResponseCode::OK, $created),
    new MappedResponse(ResponseCode::NOT_FOUND, $notFound->createReference()),
);
```

The components' responses are a `ResponseMap`, under their names, while an operation's are a `ResponseCollection`,
under their response codes.

## Reusing a header
A header that many responses have can be defined once in the components, with a `NamedHeader`, and referenced from
each. The components' headers are a `HeaderMap`, under their component names, while a response's are a
`HeaderCollection`, under their HTTP names:

```php
use Programster\Swagger\HeaderMap;
use Programster\Swagger\NamedHeader;

$rateLimit = new NamedHeader("RateLimit", Header::createFromSchema(new SchemaInt(), description: "Requests left."));
$componentsWithHeader = new Components(headers: new HeaderMap($rateLimit));

$limited = new Response(
    description: "A page of users.",
    headers: new HeaderCollection(new MappedHeader("X-Rate-Limit", $rateLimit->createReference())),
);
```
