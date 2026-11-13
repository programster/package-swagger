<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * A security scheme in which the client sends a username and password with HTTP basic authentication.
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class HttpBasicAuth implements SecuritySchemeInterface
{
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     */
    public function __construct(?string $description = null, ExtensionMap $extensions = new ExtensionMap())
    {
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "http",
            'description' => $this->m_description,
            'scheme' => "basic",
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
