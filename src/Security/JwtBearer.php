<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * A security scheme in which the client sends a JWT as a bearer token, in the Authorization header. For a token in
 * another format, use HttpBearer.
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class JwtBearer implements SecuritySchemeInterface
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
            'scheme' => "bearer",
            'bearerFormat' => "JWT",
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
