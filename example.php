<?php

/* 
 * A script to generate the swagger JSON documentation.
 */


require_once(__DIR__ . '/vendor/autoload.php');




// add a path/endpoint for users



$successResponse = new \Programster\Swagger\Response(200, "User successfully created.");


$firstNameParameter = new \Programster\Swagger\Parameter(
    "first_name", 
    "The first name of the user", 
    true, 
    \Programster\Swagger\Type::createString(), 
    Programster\Swagger\ParameterLocation::createBody()
);

$lastNameParameter = new \Programster\Swagger\Parameter(
    "last_name", 
    "The last name of the user", 
    true, 
    \Programster\Swagger\Type::createString(), 
    Programster\Swagger\ParameterLocation::createBody()
);

$createUserParameters = new Programster\Swagger\ParameterCollection($firstNameParameter, $lastNameParameter);

// add a "POST" action to the users path for creating a user
$createUserAction = new Programster\Swagger\PathAction(
    "post", 
    "Add a user to the system", 
    "Add a user to the system",
    $createUserParameters,
    new \Programster\Swagger\ResponseCollection($successResponse)
);

$usersPath = new \Programster\Swagger\Path("/users", $createUserAction);
$paths = new Programster\Swagger\PathCollection($usersPath);


$userProperties = array(
    new Programster\Swagger\DefinitionProperty("id", \Programster\Swagger\Type::createInt(), "The ID of the user."),
    new Programster\Swagger\DefinitionProperty("first_name", \Programster\Swagger\Type::createString(), "The first name of the user."),
    new Programster\Swagger\DefinitionProperty("last_name", \Programster\Swagger\Type::createString(), "The last name of the user."),
);

$userDefinition = new Programster\Swagger\Definition("User", "A user object", ...$userProperties);

$definitions = new Programster\Swagger\DefinitionCollection($userDefinition);

$document = new \Programster\Swagger\Document(
    "My RESTful API", 
    "A service that does something useful.",
    "www.myAPiHOstname.com", 
    "1.0.0", 
    $paths, 
    $definitions
);


file_put_contents(__DIR__ . '/swagger.json', $document);
print "Swagger documentation updated. Don't forget to commit." . PHP_EOL;