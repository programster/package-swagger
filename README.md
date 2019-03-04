A PHP package to simplify the generation of Swagger 2.0 JSON documentation.

The [specification for Swagger 2.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md) can be found here.

## Example Usage
Below is a basic example for setting up an API that has an endpoint for creating users.
It should be pretty self explanatory if you are using an IDE with auto-complete. 
All that you need to know is that you need to create a \Programster\Swagger\Document object. 
Once you have managed to create it, you can just print it.


```php
<?php

/* 
 * A script to generate the swagger JSON documentation.
 */


require_once(__DIR__ . '/vendor/autoload.php');

// add a path/endpoint for users
$userReference = array();
$userReference['$ref'] = "#definitions/user";

$userProperties = array(
    new Programster\Swagger\DefinitionProperty("id", \Programster\Swagger\Type::createInt(), "The ID of the user."),
    new Programster\Swagger\DefinitionProperty("first_name", \Programster\Swagger\Type::createString(), "The first name of the user."),
    new Programster\Swagger\DefinitionProperty("last_name", \Programster\Swagger\Type::createString(), "The last name of the user."),
);

$userDefinition = new Programster\Swagger\Definition("User", "A user object", ...$userProperties);
$successResponse = new \Programster\Swagger\Response(200, "User successfully created.", $userDefinition);

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
    "post", 
    "Add a user to the system", 
    "Add a user to the system",
    $createUserParameters,
    new \Programster\Swagger\ResponseCollection($successResponse)
);

$usersPath = new \Programster\Swagger\Path("/users", $createUserAction);
$paths = new Programster\Swagger\PathCollection($usersPath);


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
```

If you were to run that script, you would generate a `swagger.json` output file which you can link to from 
[a swagger-UI page](https://petstore.swagger.io/) (as long as you enable CORS), or you can paste it directly into an 
online [swagger editor](https://editor.swagger.io/).