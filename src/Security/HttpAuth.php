<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * A security scheme that uses one of the other HTTP authentication schemes, e.g. digest. Basic and bearer
 * authentication have their own security schemes: HttpBasicAuth, and HttpBearer (or JwtBearer).
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class HttpAuth implements SecuritySchemeInterface
{
    private HttpAuthScheme $m_scheme;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param HttpAuthScheme $scheme - the HTTP authentication scheme, e.g. HttpAuthScheme::DIGEST.
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     */
    public function __construct(
        HttpAuthScheme $scheme,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $this->m_scheme = $scheme;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "http",
            'description' => $this->m_description,
            'scheme' => $this->m_scheme->value,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
