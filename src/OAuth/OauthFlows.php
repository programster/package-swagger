<?php

namespace Programster\Swagger\OAuth;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * The OAuth flows that an OAuth2 security scheme supports. There must be at least one.
 * https://swagger.io/specification/v3/#oauth-flows-object
 */
final readonly class OauthFlows implements \JsonSerializable
{
    private ?ImplicitFlow $m_implicit;
    private ?PasswordFlow $m_password;
    private ?ClientCredentialsFlow $m_clientCredentials;
    private ?AuthorizationCodeFlow $m_authorizationCode;
    private ExtensionMap $m_extensions;


    /**
     * @param ImplicitFlow|null $implicit - the implicit flow, if it is supported.
     * @param PasswordFlow|null $password - the resource owner password flow, if it is supported.
     * @param ClientCredentialsFlow|null $clientCredentials - the client credentials flow, if it is supported.
     * @param AuthorizationCodeFlow|null $authorizationCode - the authorization code flow, if it is supported.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the flows.
     * @throws InvalidValueException - if none of the flows are given.
     */
    public function __construct(
        ?ImplicitFlow $implicit = null,
        ?PasswordFlow $password = null,
        ?ClientCredentialsFlow $clientCredentials = null,
        ?AuthorizationCodeFlow $authorizationCode = null,
        ExtensionMap $extensions = new ExtensionMap(),
    )
    {
        if (
               $implicit === null
            && $password === null
            && $clientCredentials === null
            && $authorizationCode === null
        )
        {
            throw new InvalidValueException("OauthFlows needs at least one flow.");
        }

        $this->m_implicit = $implicit;
        $this->m_password = $password;
        $this->m_clientCredentials = $clientCredentials;
        $this->m_authorizationCode = $authorizationCode;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Check whether any of the flows offers a scope, as a security requirement can only ask for the scopes that its
     * scheme's flows offer.
     * @param string $scope - the name of the scope.
     * @return bool
     */
    public function hasScope(string $scope) : bool
    {
        $flows = [$this->m_implicit, $this->m_password, $this->m_clientCredentials, $this->m_authorizationCode];
        $found = false;

        foreach ($flows as $flow)
        {
            if ($flow !== null && $flow->getScopes()->hasKey($scope))
            {
                $found = true;
            }
        }

        return $found;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'implicit' => $this->m_implicit,
            'password' => $this->m_password,
            'clientCredentials' => $this->m_clientCredentials,
            'authorizationCode' => $this->m_authorizationCode,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
