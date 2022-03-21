<?php

/*
 * A script to generate the swagger JSON documentation.
 */

namespace Programster\Swagger\Testing;
use Programster\Swagger\Definition;
use Programster\Swagger\DefinitionCollection;
use Programster\Swagger\DefinitionProperty;
use Programster\Swagger\Document;
use Programster\Swagger\Method;
use Programster\Swagger\Parameter;
use Programster\Swagger\ParameterBodyObject;
use Programster\Swagger\ParameterCollection;
use Programster\Swagger\Path;
use Programster\Swagger\PathAction;
use Programster\Swagger\PathCollection;
use Programster\Swagger\ParameterLocation;
use Programster\Swagger\Response;
use Programster\Swagger\ResponseCollection;
use Programster\Swagger\SecurityScheme;
use Programster\Swagger\SecuritySchemeCollection;
use Programster\Swagger\Type;


require_once(__DIR__ . '/../vendor/autoload.php');


// add a path/endpoint for users


$userReference = array();
$userReference['$ref'] = "#definitions/user";

$userProperties = array(
    DefinitionProperty::createBasic("id", Type::INT, "The ID of the user.", false),
    DefinitionProperty::createBasic("first_name", Type::INT, "The first name of the user.", true),
    DefinitionProperty::createBasic("last_name", Type::INT, "The last name of the user.", true),
);

$userDefinition = new Definition("User", "A user object", ...$userProperties);

$createUserSuccessResponse = Response::createObjectResponse(
    200,
    "User successfully created.",
    $userDefinition
);


$firstNameParameter = new Parameter(
    "first_name",
    "The first name of the user",
    true,
    Type::STRING,
    ParameterLocation::FORM_DATA
);

$lastNameParameter = new Parameter(
    "last_name",
    "The last name of the user",
    true,
    Type::STRING,
    ParameterLocation::FORM_DATA
);

$createUserParameters = new ParameterCollection($firstNameParameter, $lastNameParameter);

// create user with posted form fields...
$createUserAction = new PathAction(
    Method::POST,
    "Get users in the system",
    "Get users in the system",
    $createUserParameters,
    new ResponseCollection($createUserSuccessResponse)
);



$usersPath = new Path("/users", $createUserAction);
$paths = new PathCollection($usersPath);
$definitions = new DefinitionCollection($userDefinition);
$basicAuth = SecurityScheme::createBasicAuth("basicAuth");

$document = new Document(
    "My RESTful API",
    "A service that does something useful.",
    "www.myAPiHOstname.com",
    "1.0.0",
    $paths,
    $definitions,
    new SecuritySchemeCollection($basicAuth),
    array("basicAuth" => array())
);


//file_put_contents(__DIR__ . '/swagger.json', $document);
print $document . PHP_EOL;
//print "Swagger documentation updated. Don't forget to commit." . PHP_EOL;
