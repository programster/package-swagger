<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A security scheme in which the client sends an API key, in a header, the query string or a cookie.
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class ApiKey implements SecuritySchemeInterface
{
    private string $m_name;
    private ApiKeyLocation $m_in;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string $name - the name of the header, query parameter or cookie that the key is sent in.
     *
     * @param ApiKeyLocation $in - where the key is sent.
     *
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     *
     * @throws InvalidValueException - if the name is empty.
     */
    public function __construct(
        string $name,
        ApiKeyLocation $in,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($name, "The API key's name");
        $this->m_name = $name;
        $this->m_in = $in;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "apiKey",
            'description' => $this->m_description,
            'name' => $this->m_name,
            'in' => $this->m_in->value,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
