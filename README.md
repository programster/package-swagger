A PHP package to simplify the generation of Swagger 2.0 JSON documentation.

The [specification for Swagger 2.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md) can be found here.

## Example Usage
Below is a basic example for setting up an API that has an endpoint for creating users.
It should be pretty self explanatory if you are using an IDE with auto-complete.
All that you need to know is that you need to create a \Programster\Swagger\Document object.
Once you have managed to create it, you can just print it.


If you were to run that script, you would generate a `swagger.json` output file which you can link to from
[a swagger-UI page](https://petstore.swagger.io/) (as long as you enable CORS), or you can paste it directly into an
online [swagger editor](https://editor.swagger.io/).
