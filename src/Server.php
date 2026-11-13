<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A server that the API is available on.
 * https://swagger.io/specification/v3/#server-object
 */
final readonly class Server implements \JsonSerializable
{
    private string $m_url;
    private ?string $m_description;
    private ServerVariableMap $m_variables;
    private ExtensionMap $m_extensions;


    /**
     * @param string $url - the URL of the server, e.g. "https://api.example.com/v1". It may be relative to where the
     * document is (e.g. "/v1"), and may have variables in braces, e.g. "https://{environment}.example.com".
     *
     * @param string|null $description - a description of the server, e.g. "Production". CommonMark syntax may be
     * used.
     *
     * @param ServerVariableMap $variables - the variables in the URL. There must be one for each variable in the URL,
     * and no others.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the server.
     *
     * @throws InvalidValueException - if the URL is empty, or the variables don't match the ones in the URL.
     */
    public function __construct(
        string $url,
        ?string $description = null,
        ServerVariableMap $variables = new ServerVariableMap(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($url, "The server's URL");
        preg_match_all('/\{([^}]*)\}/', $url, $matches);
        $namesInUrl = array_unique($matches[1]);
        $definedNames = $variables->getKeys();

        foreach ($namesInUrl as $name)
        {
            if (!in_array($name, $definedNames, true))
            {
                $message = "The server URL \"{$url}\" has a {{$name}} variable, but no ServerVariable named "
                    . "\"{$name}\" was given for it.";

                throw new InvalidValueException($message);
            }
        }

        foreach ($definedNames as $name)
        {
            if (!in_array($name, $namesInUrl, true))
            {
                $message = "A ServerVariable named \"{$name}\" was given, but the server URL \"{$url}\" doesn't have a "
                    . "{{$name}} variable to use it in.";

                throw new InvalidValueException($message);
            }
        }

        $this->m_url = $url;
        $this->m_description = $description;
        $this->m_variables = clone $variables;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'url' => $this->m_url,
            'description' => $this->m_description,
            'variables' => $this->m_variables->isEmpty() ? null : $this->m_variables,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
