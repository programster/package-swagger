<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * The license that the API is under.
 * https://swagger.io/specification/v3/#license-object
 */
final readonly class License implements \JsonSerializable
{
    private string $m_name;
    private ?string $m_url;
    private ExtensionMap $m_extensions;


    /**
     * @param string $name - the name of the license, e.g. "MIT".
     * @param string|null $url - the URL of the license's text.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the license.
     * @throws InvalidValueException - if the name is empty, or the URL isn't a URL.
     */
    public function __construct(string $name, ?string $url = null, ExtensionMap $extensions = new ExtensionMap())
    {
        Validator::assertNotEmpty($name, "The license's name");

        if ($url !== null)
        {
            Validator::assertUrl($url, "The license's URL");
        }

        $this->m_name = $name;
        $this->m_url = $url;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'name' => $this->m_name,
            'url' => $this->m_url,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
