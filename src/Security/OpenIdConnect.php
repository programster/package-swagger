<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A security scheme that uses OpenID Connect, whose details (including the scopes it offers) are found from its
 * discovery URL.
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class OpenIdConnect implements SecuritySchemeInterface
{
    private string $m_openIdConnectUrl;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string $openIdConnectUrl - the URL of the OpenID Connect discovery document, e.g.
     * "https://example.com/.well-known/openid-configuration".
     *
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     *
     * @throws InvalidValueException - if the URL isn't a URL.
     */
    public function __construct(
        string $openIdConnectUrl,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertUrl($openIdConnectUrl, "The OpenID Connect URL");
        $this->m_openIdConnectUrl = $openIdConnectUrl;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "openIdConnect",
            'description' => $this->m_description,
            'openIdConnectUrl' => $this->m_openIdConnectUrl,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
