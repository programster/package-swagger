<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\OAuth\OauthFlows;


/**
 * A security scheme that uses OAuth 2, with one or more flows.
 * https://swagger.io/specification/v3/#security-scheme-object
 */
final readonly class OAuth2 implements SecuritySchemeInterface
{
    private OauthFlows $m_flows;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param OauthFlows $flows - the flows that the scheme supports, and the scopes they offer.
     * @param string|null $description - a description of the scheme. CommonMark syntax may be used.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the scheme.
     */
    public function __construct(
        OauthFlows $flows,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $this->m_flows = $flows;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Check whether any of the flows offers a scope.
     * @param string $scope - the name of the scope.
     * @return bool
     */
    public function hasScope(string $scope) : bool
    {
        return $this->m_flows->hasScope($scope);
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => "oauth2",
            'description' => $this->m_description,
            'flows' => $this->m_flows,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
