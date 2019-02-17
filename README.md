A PHP package to simplify the generation of swagger JSON documentation.

## Example Usage
Below is a really basic example of using the code to get you started. It should be pretty self explanatory if you 
are using an IDE with auto-complete.

```php
<?php

/* 
 * A script to generate the swagger JSON documentation.
 */

require_once(__DIR__ . '/vendor/autoload.php');


$document = new \Programster\Swagger\Document(
    "My RESTful API", 
    "A service that does something useful.", 
    $host = "www.myAPiHOstname.com", 
    "1.0.0", 
    $schemes = array("http", "https"), 
    $basePath = "/api"
);

// add a path/endpoint for users
$path = new \Programster\Swagger\Path("/users");

// add a "POST" action to the users path for creating a user
$createUserAction = new Programster\Swagger\PathAction(
    "post", 
    "Add a user to the system", 
    "Add a user to the syste"
);

$successResponse = new \Programster\Swagger\Response(200, "User successfully created.");
$createUserAction->addResponse($successResponse);
$path->addAction($createUserAction);
$document->addPath($path);

file_put_contents(__DIR__ . '/swagger.json', $document);
print "Swagger documentation updated. Don't forget to commit." . PHP_EOL;
```

If you were to run that script, you would generate a `swagger.json` output file which you can link to from [a swagger-UI page](https://petstore.swagger.io/) (as long as you enable CORS), or you can paste it directly into an online [swagger editor](https://editor.swagger.io/).