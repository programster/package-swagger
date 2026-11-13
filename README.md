# Swagger
[![Tests](https://github.com/programster/package-swagger/actions/workflows/tests.yml/badge.svg)](https://github.com/programster/package-swagger/actions/workflows/tests.yml)

## Contents
- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- Guides
    - [Getting started](docs/getting-started.md): how a document fits together, and how to output it
    - [Paths and operations](docs/paths-and-operations.md): paths, operations, tags and callbacks
    - [Parameters](docs/parameters.md): path, query, header and cookie parameters, and their styles
    - [Schemas](docs/schemas.md): strings, numbers, booleans, arrays, objects, maps, enums, and combining schemas
      with allOf, oneOf and anyOf
    - [Request bodies, content and examples](docs/content.md): media types, examples and encodings
    - [Responses, headers and links](docs/responses.md): response codes and ranges, headers, and links to other
      operations
    - [Components and references](docs/components-and-references.md): defining things once and reusing them
    - [Security](docs/security.md): API keys, HTTP auth, OAuth 2, OpenID Connect, and which operations need them
    - [Servers](docs/servers.md): where the API is, including URLs with variables
    - [Specification extensions](docs/extensions.md): adding `x-` fields for tools such as Redoc
    - [Errors and checks](docs/errors.md): the exceptions, and every mistake the package catches
- [Upgrading from version 3](docs/upgrading-to-4.md)
- [Testing](docs/testing.md)
- [Validating a document](#validating-a-document)
- [Design principles](#design-principles)
- [License](#license)

## About
A PHP package for generating [OpenAPI 3.0.3](https://swagger.io/specification/v3/) documents (formerly known as
Swagger documents), which describe an API so that tools such as Swagger UI can display it, and others can generate
clients for it.

You build the document from PHP objects (an `Operation`, a `Parameter`, a `SchemaString`, and so on), then output it
as JSON or YAML. Each object checks itself as it is created, so a mistake throws an exception on the line that made
it, rather than producing a document that a tool rejects later. For example:

- a path parameter can't be optional, and a GET operation can't have a request body,
- a security requirement can't ask for an OAuth scope that its scheme doesn't offer,
- a reference to a schema can't be used where a parameter is expected,
- and the document checks that every path's `{variables}` have parameters, and that every security scheme that is
  used is defined.

See [Errors and checks](docs/errors.md) for the full list.

## Requirements
- PHP 8.2 or later (8.2, 8.3, 8.4 and 8.5 are tested).
- The `yaml` extension, for `OpenApi::toYaml()`.
- [ramsey/collection](https://github.com/ramsey/collection), which Composer installs for you.

## Installation
```bash
composer require programster/swagger
```

## Quick start
This creates a document for an API with one operation, which gets a user by their ID:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Programster\Swagger\ContentMap;
use Programster\Swagger\Info;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaString;

$user = new SchemaObject(properties: new PropertyMap(
    new Property("id", new SchemaString(), required: true),
    new Property("name", new SchemaString(), required: true),
));

$getUser = new Operation(
    method: Method::GET,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response("The user.", new ContentMap(new MediaType(MimeType::JSON, $user)))),
        new MappedResponse(ResponseCode::NOT_FOUND, new Response("There is no user with that ID.")),
    ),
    summary: "Get a user.",
    parameters: new ParameterCollection(Parameter::createFromSchema("id", ParameterLocation::PATH, new SchemaString())),
);

$document = new OpenApi(
    info: new Info("Users API", "1.0.0"),
    paths: new PathCollection(new Path("/users/{id}", new PathItem(new OperationCollection($getUser)))),
);

echo $document->toJson();
```

Which outputs:

```json
{
    "openapi": "3.0.3",
    "info": {
        "title": "Users API",
        "version": "1.0.0"
    },
    "paths": {
        "/users/{id}": {
            "get": {
                "summary": "Get a user.",
                "parameters": [
                    {
                        "name": "id",
                        "in": "path",
                        "required": true,
                        "schema": {
                            "type": "string"
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "The user.",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "id": {
                                            "type": "string"
                                        },
                                        "name": {
                                            "type": "string"
                                        }
                                    },
                                    "required": [
                                        "id",
                                        "name"
                                    ]
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "There is no user with that ID."
                    }
                }
            }
        }
    }
}
```

The [Getting started](docs/getting-started.md) guide goes through how a document fits together, a piece at a time.

## Validating a document
The package checks a lot as the document is built, but it can't check everything (e.g. that a reference points at
something that exists, or that an example matches its schema). To check a finished document against the OpenAPI
specification, use [Redocly CLI](https://redocly.com/docs/cli/), which needs Node.js:

```bash
npx @redocly/cli lint /path/to/openapi.json
```

Or with Docker, from the directory the document is in:

```bash
docker run --rm -v "$PWD":/d:ro -w /d node:22-slim npx --yes @redocly/cli lint openapi.json
```

It reports two kinds of problem. **Errors** mean the document isn't valid OpenAPI, or something in it is wrong (e.g.
an example that doesn't match its schema), and should be fixed. **Warnings** are Redocly's advice about designing an
API, such as every operation having a 4XX response, which you can follow or turn off in a
[`redocly.yaml`](https://redocly.com/docs/cli/configuration/) file.

The package's own output is checked this way on every push. See [Testing](docs/testing.md#validating-the-output).

## Design principles
- **Mistakes can't be made, or are caught straight away.** Where possible, the types make a mistake impossible to
  write (e.g. `ApiKeyLocation` has no `PATH`, as an API key can't go in the path, and a `SchemaReference` can only
  go where a schema can). Otherwise, the constructor throws, so the exception points at the line with the mistake.
- **Objects are complete and can't change.** Every object is given everything in its constructor, and is `readonly`.
  The collections (e.g. `PathCollection`) are [ramsey/collection](https://github.com/ramsey/collection) collections,
  so they can be added to, but an object takes a copy of any collection it is given, so changing the collection
  afterwards doesn't change the object.
- **Nothing is accepted that wouldn't have an effect.** E.g. a response can't be given a `Content-Type` header, as
  OpenAPI ignores it, and a flow only takes the URLs that OpenAPI gives that flow.
- **The output is valid OpenAPI 3.0.3.** E.g. empty maps are output as `{}` rather than `[]`, and response codes are
  quoted in YAML.

## License
MIT. See [LICENSE](LICENSE).
