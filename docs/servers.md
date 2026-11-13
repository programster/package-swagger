# Servers

- [Servers](#servers)
- [Variables in the URL](#variables-in-the-url)
- [Where servers can go](#where-servers-can-go)

## Servers
A server is where the API is. Its URL can be absolute, or relative to where the document is served from:

```php
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;

$servers = new ServerCollection(
    new Server("https://api.example.com/v1", "Production"),
    new Server("https://staging-api.example.com/v1", "Staging"),
    new Server("/v1", "The same server as the document"),
);
```

When the document has no servers, the API is on the server that the document is served from.

## Variables in the URL
A URL can have variables in braces, which are filled in from `ServerVariable`s. Each has a default, and can be
limited to some values:

```php
use Programster\Swagger\ServerVariable;
use Programster\Swagger\ServerVariableMap;

$regional = new Server(
    url: "https://{region}.api.example.com:{port}/v1",
    description: "The regional servers.",
    variables: new ServerVariableMap(
        new ServerVariable("region", "eu", "Where the server is.", ["eu", "us", "asia"]),
        new ServerVariable("port", "443"),
    ),
);
```

Every variable in the URL must have a `ServerVariable`, and every `ServerVariable` must be in the URL, which the
`Server` checks:

```php
$missing = new Server("https://{region}.api.example.com"); // Throws InvalidValueException
```

A variable's values can't be empty, must be strings, and must include its default.

## Where servers can go
The document's servers are used for every operation. A path item or an operation can have its own servers instead,
e.g. for an upload that goes to another server:

```php
use Programster\Swagger\Info;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\Method;
use Programster\Swagger\OpenApi;
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

$upload = new Operation(
    Method::POST,
    $ok,
    servers: new ServerCollection(new Server("https://uploads.example.com"))
);

$document = new OpenApi(
    info: new Info("Files API", "1.0"),
    paths: new PathCollection(
        new Path(
            "/files",
            new PathItem(new OperationCollection($upload))
        )
    ),
    servers: $servers,
);
```
