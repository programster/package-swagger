<?php

/*
 * Builds a document that uses every feature of the package, so that testing/validate-with-redocly.sh can check that
 * what the package outputs is valid OpenAPI 3.0.3. It writes the document as JSON and YAML, and a path item that the
 * document references, into the directory it is given, e.g.:
 *   php testing/validation/build-documents.php /tmp/documents
 */

require_once(__DIR__ . '/../../vendor/autoload.php');

use Programster\Swagger\Callback;
use Programster\Swagger\CallbackMap;
use Programster\Swagger\Components;
use Programster\Swagger\Contact;
use Programster\Swagger\ContentMap;
use Programster\Swagger\Encoding;
use Programster\Swagger\EncodingMap;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\NamedExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Header;
use Programster\Swagger\HeaderCollection;
use Programster\Swagger\HeaderMap;
use Programster\Swagger\Info;
use Programster\Swagger\License;
use Programster\Swagger\Link;
use Programster\Swagger\LinkMap;
use Programster\Swagger\MappedHeader;
use Programster\Swagger\MappedResponse;
use Programster\Swagger\MediaType;
use Programster\Swagger\Method;
use Programster\Swagger\MimeType;
use Programster\Swagger\NamedCallback;
use Programster\Swagger\NamedHeader;
use Programster\Swagger\NamedLink;
use Programster\Swagger\NamedParameter;
use Programster\Swagger\NamedRequestBody;
use Programster\Swagger\NamedResponse;
use Programster\Swagger\OAuth\AuthorizationCodeFlow;
use Programster\Swagger\OAuth\ClientCredentialsFlow;
use Programster\Swagger\OAuth\ImplicitFlow;
use Programster\Swagger\OAuth\OauthFlows;
use Programster\Swagger\OAuth\PasswordFlow;
use Programster\Swagger\OAuth\Scope;
use Programster\Swagger\OAuth\ScopeMap;
use Programster\Swagger\OpenApi;
use Programster\Swagger\Operation;
use Programster\Swagger\OperationCollection;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\ParameterMap;
use Programster\Swagger\Path;
use Programster\Swagger\PathCollection;
use Programster\Swagger\PathItem;
use Programster\Swagger\References\PathItemReference;
use Programster\Swagger\RequestBody;
use Programster\Swagger\RequestBodyMap;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCode;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\ResponseMap;
use Programster\Swagger\Schemas\Access;
use Programster\Swagger\Schemas\Discriminator;
use Programster\Swagger\Schemas\DiscriminatorMapping;
use Programster\Swagger\Schemas\DiscriminatorMappingMap;
use Programster\Swagger\Schemas\IntegerFormat;
use Programster\Swagger\Schemas\NamedSchema;
use Programster\Swagger\Schemas\NumberFormat;
use Programster\Swagger\Schemas\Property;
use Programster\Swagger\Schemas\PropertyMap;
use Programster\Swagger\Schemas\SchemaAllOf;
use Programster\Swagger\Schemas\SchemaAnyOf;
use Programster\Swagger\Schemas\SchemaAnyValue;
use Programster\Swagger\Schemas\SchemaArray;
use Programster\Swagger\Schemas\SchemaBool;
use Programster\Swagger\Schemas\SchemaCollection;
use Programster\Swagger\Schemas\SchemaInt;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Schemas\SchemaNot;
use Programster\Swagger\Schemas\SchemaNumber;
use Programster\Swagger\Schemas\SchemaObject;
use Programster\Swagger\Schemas\SchemaOneOf;
use Programster\Swagger\Schemas\SchemaString;
use Programster\Swagger\Schemas\StringFormat;
use Programster\Swagger\Schemas\Xml;
use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\ApiKeyLocation;
use Programster\Swagger\Security\HttpAuth;
use Programster\Swagger\Security\HttpAuthScheme;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\HttpBearer;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NamedSecurityScheme;
use Programster\Swagger\Security\OAuth2;
use Programster\Swagger\Security\OpenIdConnect;
use Programster\Swagger\Security\RequiredScheme;
use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;
use Programster\Swagger\Security\SecuritySchemeMap;
use Programster\Swagger\Server;
use Programster\Swagger\ServerCollection;
use Programster\Swagger\ServerVariable;
use Programster\Swagger\ServerVariableMap;
use Programster\Swagger\Style;
use Programster\Swagger\Tag;
use Programster\Swagger\TagCollection;

if ($argc !== 2 || !is_dir($argv[1]))
{
    fwrite(STDERR, "Usage: php build-documents.php <directory to write the documents to>\n");
    exit(1);
}

$outputDir = $argv[1];
$docs = new ExternalDocumentation("https://docs.example.org/pets", "The full guide.");
$extensions = new ExtensionMap(new Extension("x-internal-id", "pets-api"));

# Schemas
$petType = new SchemaString(description: "The kind of pet.", enum: ["cat", "dog"]);

$basePet = new NamedSchema("Pet", new SchemaObject(
    description: "What every pet has.",
    properties: new PropertyMap(
        new Property("id", new SchemaString(format: StringFormat::UUID), required: true, access: Access::READ_ONLY),
        new Property("petType", $petType, required: true),
        new Property("name", new SchemaString(minLength: 1, maxLength: 50, example: "Tiddles"), required: true),
        new Property("tags", new SchemaArray(
            items: new SchemaString(pattern: "^[a-z]+$"),
            maxItems: 10,
            uniqueItems: true,
            default: [],
            xml: new Xml(name: "tags", wrapped: true),
        )),
        new Property("metadata", new SchemaObject(additionalProperties: new SchemaAnyValue())),
    ),
    discriminator: new Discriminator("petType"),
    title: "Pet",
    externalDocs: $docs,
    xml: new Xml(name: "pet", namespace: "https://example.org/schema", prefix: "pets"),
    extensions: $extensions,
));

$catProperties = new SchemaObject(properties: new PropertyMap(
    new Property("livesLeft", new SchemaInt(minimum: 0, maximum: 9, default: 9, format: IntegerFormat::INT32)),
    new Property("indoor", new SchemaBool(default: true, example: false)),
));

$cat = new NamedSchema("Cat", new SchemaAllOf(new SchemaCollection($basePet->createReference(), $catProperties)));

$dogProperties = new SchemaObject(properties: new PropertyMap(
    new Property("weightKg", new SchemaNumber(
        minimum: 0,
        exclusiveMinimum: true,
        multipleOf: 0.1,
        format: NumberFormat::FLOAT,
        example: 12.5,
    )),
    new Property("size", new SchemaString(nullable: true, enum: ["small", "large"])),
));

$dog = new NamedSchema("Dog", new SchemaAllOf(new SchemaCollection($basePet->createReference(), $dogProperties)));

$anyPet = new SchemaOneOf(
    schemas: new SchemaCollection($cat->createReference(), $dog->createReference()),
    description: "A cat or a dog.",
    discriminator: new Discriminator("petType", new DiscriminatorMappingMap(
        new DiscriminatorMapping("cat", $cat->createReference()),
        new DiscriminatorMapping("dog", $dog->createReference()),
    )),
);

$newPet = new NamedSchema("NewPet", new SchemaObject(
    properties: new PropertyMap(
        new Property("petType", $petType, required: true),
        new Property("name", new SchemaString(), required: true),
        new Property("ownerPassword", new SchemaString(format: StringFormat::PASSWORD), access: Access::WRITE_ONLY),
        new Property("photo", new SchemaString(format: StringFormat::BINARY)),
    ),
    additionalProperties: false,
));

$error = new NamedSchema("Error", new SchemaObject(
    properties: new PropertyMap(
        new Property("message", new SchemaString(), required: true),
        new Property("code", new SchemaAnyOf(new SchemaCollection(new SchemaInt(), new SchemaString()))),
    ),
    example: ['message' => "Something went wrong.", 'code' => -1],
));

$url = new SchemaString(format: StringFormat::URI);
$petId = new SchemaString(format: StringFormat::UUID, deprecated: false, title: "Pet ID");
$name = new SchemaNot(new SchemaString(enum: ["admin"]), description: "Any name but admin.");

# Examples, headers and other components
$tiddlesValue = ['id' => "3f2b8c1e-9a4d-4e7b-b6f0-2d5c8e1a7f93", 'petType' => "cat", 'name' => "Tiddles"];
$tiddles = new NamedExample("Tiddles", new ValueExample($tiddlesValue), summary: "A cat.");

$photoExample = new NamedExample("Photo", new ExternalValueExample("https://example.org/examples/photo.png"));

$rateLimit = new NamedHeader("RateLimit", Header::createFromSchema(
    schema: new SchemaInt(minimum: 0),
    required: true,
    description: "How many requests are left.",
    example: new ValueExample(99),
));

$traceHeader = Header::createFromContentMap(
    new ContentMap(new MediaType(MimeType::JSON, new SchemaObject(additionalProperties: new SchemaString()))),
    description: "Tracing information.",
);

$headers = new HeaderCollection(
    new MappedHeader("X-Rate-Limit", $rateLimit->createReference()),
    new MappedHeader("X-Trace", $traceHeader),
);

$errorContent = new ContentMap(new MediaType(MimeType::JSON, $error->createReference()));
$notFound = new NamedResponse("NotFound", new Response("There is no pet with that ID.", $errorContent));

$petIdParameter = new NamedParameter(
    "PetId",
    Parameter::createFromSchema("petId", ParameterLocation::PATH, $petId, description: "The pet's ID.")
);

$newPetBody = new NamedRequestBody("NewPet", new RequestBody(
    content: new ContentMap(
        new MediaType(MimeType::JSON, $newPet->createReference()),
        new MediaType(
            mimeType: "multipart/form-data",
            schema: new SchemaObject(properties: new PropertyMap(
                new Property("name", new SchemaString(), required: true),
                new Property("photo", new SchemaString(format: StringFormat::BINARY)),
            )),
            example: new ExampleMap(new NamedExample("photo", $photoExample->createReference())),
            encoding: new EncodingMap(new Encoding("photo", MimeType::PNG, $headers)),
        ),
    ),
    description: "The pet to add.",
    required: true,
));

$getPetLink = new NamedLink("GetPet", Link::createFromOperationId(
    operationId: "getPet",
    parameters: ['petId' => '$response.body#/id'],
    description: "Get the pet that was added.",
));

$petAddedCallback = new NamedCallback("PetAdded", new Callback(
    '{$request.query.callbackUrl}',
    new PathItem(new OperationCollection(new Operation(
        method: Method::POST,
        responses: new ResponseCollection(new MappedResponse(ResponseCode::NO_CONTENT, new Response("Received."))),
        summary: "A pet was added.",
        operationId: "petAdded",
        requestBody: new RequestBody(new ContentMap(new MediaType(MimeType::JSON, $anyPet)), required: true),
    ))),
));

# Security
$readScope = new Scope("pets:read", "Read pets.");
$writeScope = new Scope("pets:write", "Add and change pets.");
$scopes = new ScopeMap($readScope, $writeScope);

$oauth = new NamedSecurityScheme("oauth", new OAuth2(
    flows: new OauthFlows(
        implicit: new ImplicitFlow("https://auth.example.org/authorize", $scopes),
        password: new PasswordFlow("https://auth.example.org/token", $scopes),
        clientCredentials: new ClientCredentialsFlow("https://auth.example.org/token", $scopes),
        authorizationCode: new AuthorizationCodeFlow(
            "https://auth.example.org/authorize",
            "https://auth.example.org/token",
            $scopes,
            "https://auth.example.org/refresh"
        ),
    ),
    description: "OAuth 2.",
));

$apiKey = new NamedSecurityScheme("apiKey", new ApiKey("X-API-Key", ApiKeyLocation::HEADER, "An API key."));
$openIdUrl = "https://auth.example.org/.well-known/openid-configuration";
$openId = new NamedSecurityScheme("openId", new OpenIdConnect($openIdUrl));

$basic = new NamedSecurityScheme("basic", new HttpBasicAuth());
$jwt = new NamedSecurityScheme("jwt", new JwtBearer());
$opaqueToken = new NamedSecurityScheme("opaqueToken", new HttpBearer("opaque"));
$digest = new NamedSecurityScheme("digest", new HttpAuth(HttpAuthScheme::DIGEST));
$securitySchemes = new SecuritySchemeMap($oauth, $apiKey, $openId, $basic, $jwt, $opaqueToken, $digest);

$readSecurity = new SecurityRequirementCollection(
    $oauth->createSecurityRequirement("pets:read"),
    $openId->createSecurityRequirement("profile"),
    new SecurityRequirement(new RequiredScheme($apiKey)),
);

$writeSecurity = new SecurityRequirementCollection($oauth->createSecurityRequirement("pets:read", "pets:write"));

// Deleting a pet needs OAuth, a JWT, an opaque token, or basic and digest authentication together.
$deleteSecurity = new SecurityRequirementCollection(
    $oauth->createSecurityRequirement("pets:write"),
    $jwt->createSecurityRequirement(),
    $opaqueToken->createSecurityRequirement(),
    new SecurityRequirement(new RequiredScheme($basic), new RequiredScheme($digest)),
);

# Paths
$petContent = new ContentMap(new MediaType(
    mimeType: MimeType::JSON,
    schema: $anyPet,
    example: new ExampleMap(new NamedExample("tiddles", $tiddles->createReference())),
));

$listPets = new Operation(
    method: Method::GET,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response(
            description: "A page of pets.",
            content: new ContentMap(new MediaType(MimeType::JSON, new SchemaArray($anyPet))),
            headers: $headers,
        )),
        new MappedResponse(ResponseCode::ANY_CLIENT_ERROR, new Response("The request was wrong.", $errorContent)),
        new MappedResponse(499, new Response("The client closed the connection.")),
        new MappedResponse(ResponseCode::DEFAULT, new Response("Something went wrong.", $errorContent)),
    ),
    summary: "List the pets.",
    description: "Lists the pets, a page at a time.",
    parameters: new ParameterCollection(
        Parameter::createFromSchema(
            name: "petType",
            in: ParameterLocation::QUERY,
            schema: new SchemaArray($petType),
            description: "Only list pets of these kinds.",
            style: Style::FORM,
            explode: false,
        ),
        Parameter::createFromSchema("name", ParameterLocation::QUERY, $name, allowEmptyValue: true),
        Parameter::createFromSchema(
            name: "filter",
            in: ParameterLocation::QUERY,
            schema: new SchemaObject(additionalProperties: new SchemaString()),
            style: Style::DEEP_OBJECT,
        ),
        Parameter::createFromContentMap(
            "where",
            ParameterLocation::QUERY,
            new ContentMap(new MediaType(MimeType::JSON, new SchemaObject(additionalProperties: new SchemaString())))
        ),
        Parameter::createFromSchema("X-Request-Id", ParameterLocation::HEADER, $petId),
        Parameter::createFromSchema("session", ParameterLocation::COOKIE, new SchemaString(), deprecated: true),
    ),
    tags: ["pets"],
    operationId: "listPets",
    security: $readSecurity,
    extensions: $extensions,
);

$addPet = new Operation(
    method: Method::POST,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::CREATED, new Response(
            description: "The pet was added.",
            content: $petContent,
            links: new LinkMap($getPetLink),
        )),
        new MappedResponse(ResponseCode::BAD_REQUEST, new Response("The pet is invalid.", $errorContent)),
    ),
    summary: "Add a pet.",
    parameters: new ParameterCollection(
        Parameter::createFromSchema("callbackUrl", ParameterLocation::QUERY, $url)
    ),
    requestBody: $newPetBody->createReference(),
    tags: ["pets"],
    operationId: "addPet",
    callbacks: new CallbackMap(new NamedCallback("petAdded", $petAddedCallback->createReference())),
    security: $writeSecurity,
);

$getPet = new Operation(
    method: Method::GET,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response("The pet.", $petContent)),
        new MappedResponse(ResponseCode::NOT_FOUND, $notFound->createReference()),
    ),
    summary: "Get a pet.",
    tags: ["pets"],
    operationId: "getPet",
    externalDocs: $docs,
    security: $readSecurity,
);

$deletePet = new Operation(
    method: Method::DELETE,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::NO_CONTENT, new Response("The pet was deleted.")),
        new MappedResponse(ResponseCode::NOT_FOUND, $notFound->createReference()),
    ),
    summary: "Delete a pet.",
    tags: ["pets"],
    operationId: "deletePet",
    deprecated: true,
    security: $deleteSecurity,
);

$healthItem = new PathItem(new OperationCollection(new Operation(
    method: Method::GET,
    responses: new ResponseCollection(
        new MappedResponse(ResponseCode::OK, new Response("The API is up.")),
        new MappedResponse(ResponseCode::ANY_SERVER_ERROR, new Response("The API is down.")),
    ),
    summary: "Check the API is up.",
    tags: ["health"],
    operationId: "getHealth",
    security: new SecurityRequirementCollection(),
)));

$paths = new PathCollection(
    new Path("/pets", new PathItem(new OperationCollection($listPets, $addPet), summary: "The pets.")),
    new Path("/pets/{petId}", new PathItem(
        operations: new OperationCollection($getPet, $deletePet),
        parameters: new ParameterCollection($petIdParameter->createReference()),
    )),
    new Path("/health", PathItemReference::createToDocument("health.json")),
);

# The document
$document = new OpenApi(
    info: new Info(
        title: "Pets API",
        version: "1.0.0",
        description: "An API that uses **every** feature of the package.",
        termsOfService: "https://example.org/terms",
        contact: new Contact("Support", "https://example.org/support", "support@example.org"),
        license: new License("MIT", "https://opensource.org/licenses/MIT"),
        extensions: new ExtensionMap(new Extension("x-logo", ['url' => "https://example.org/logo.png"])),
    ),
    paths: $paths,
    servers: new ServerCollection(
        new Server(
            url: "https://{environment}.pets.example.org/v1",
            description: "The API.",
            variables: new ServerVariableMap(new ServerVariable("environment", "api", enum: ["api", "staging"])),
        ),
    ),
    components: new Components(
        schemas: new SchemaMap($basePet, $cat, $dog, $newPet, $error),
        responses: new ResponseMap($notFound),
        parameters: new ParameterMap($petIdParameter),
        examples: new ExampleMap($tiddles, $photoExample),
        requestBodies: new RequestBodyMap($newPetBody),
        headers: new HeaderMap($rateLimit),
        securitySchemes: $securitySchemes,
        links: new LinkMap($getPetLink),
        callbacks: new CallbackMap($petAddedCallback),
    ),
    security: $readSecurity,
    tags: new TagCollection(
        new Tag("pets", "Everything about pets.", $docs),
        new Tag("health", "Whether the API is up."),
    ),
    externalDocs: $docs,
    extensions: new ExtensionMap(new Extension("x-tagGroups", [['name' => "API", 'tags' => ["pets", "health"]]])),
);

file_put_contents("{$outputDir}/all-features.json", $document->toJson());
file_put_contents("{$outputDir}/all-features.yaml", $document->toYaml());
file_put_contents("{$outputDir}/health.json", json_encode($healthItem, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
