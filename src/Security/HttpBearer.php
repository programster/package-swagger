<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A security scheme in which the client sends a bearer token in the Authorization header. For a JWT, JwtBearer is
 * the same as this with a bearerFormat of "JWT".
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class HttpBearer implements SecuritySchemeInterface
{
    private ?string $m_bearerFormat;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $bearerFormat - a hint as to the format of the token, e.g. "JWT". It is only for
     * documentation.
     *
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     *
     * @throws InvalidValueException - if the bearer format is an empty string.
     */
    public function __construct(
        ?string $bearerFormat = null,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if ($bearerFormat !== null)
        {
            Validator::assertNotEmpty($bearerFormat, "The bearer format");
        }

        $this->m_bearerFormat = $bearerFormat;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "http",
            'description' => $this->m_description,
            'scheme' => "bearer",
            'bearerFormat' => $this->m_bearerFormat,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
