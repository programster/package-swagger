<?php

/* 
 * A script to generate the swagger JSON documentation.
 */


require_once(__DIR__ . '/vendor/autoload.php');




// add a path/endpoint for users



$userReference = array();
$userReference['$ref'] = "#definitions/user";

$userProperties = array(
    Programster\Swagger\DefinitionProperty::createBasic("id", \Programster\Swagger\Type::createInt(), "The ID of the user."),
    Programster\Swagger\DefinitionProperty::createBasic("first_name", \Programster\Swagger\Type::createString(), "The first name of the user."),
    Programster\Swagger\DefinitionProperty::createBasic("last_name", \Programster\Swagger\Type::createString(), "The last name of the user."),
);

$userDefinition = new Programster\Swagger\Definition("User", "A user object", ...$userProperties);
$successResponse = Programster\Swagger\Response::createObjectResponse(200, "User successfully created.", $userDefinition);

$firstNameParameter = new \Programster\Swagger\Parameter(
    "first_name", 
    "The first name of the user", 
    true, 
    \Programster\Swagger\Type::createString(), 
    Programster\Swagger\ParameterLocation::createFormData()
);

$lastNameParameter = new \Programster\Swagger\Parameter(
    "last_name", 
    "The last name of the user", 
    true, 
    \Programster\Swagger\Type::createString(), 
    Programster\Swagger\ParameterLocation::createFormData()
);

$createUserParameters = new Programster\Swagger\ParameterCollection($firstNameParameter, $lastNameParameter);

// add a "POST" action to the users path for creating a user
$createUserAction = new Programster\Swagger\PathAction(
    Programster\Swagger\Method::createPost(), 
    "Add a user to the system", 
    "Add a user to the system",
    $createUserParameters,
    new \Programster\Swagger\ResponseCollection($successResponse)
);

$usersPath = new \Programster\Swagger\Path("/users", $createUserAction);
$paths = new Programster\Swagger\PathCollection($usersPath);
$definitions = new Programster\Swagger\DefinitionCollection($userDefinition);
$basicAuth = Programster\Swagger\SecurityScheme::createBasicAuth("basicAuth");

$document = new \Programster\Swagger\Document(
    "My RESTful API", 
    "A service that does something useful.",
    "www.myAPiHOstname.com", 
    "1.0.0", 
    $paths, 
    $definitions,
    new \Programster\Swagger\SecuritySchemeCollection($basicAuth),
    array("basicAuth" => array())
);


file_put_contents(__DIR__ . '/swagger.json', $document);
print "Swagger documentation updated. Don't forget to commit." . PHP_EOL;