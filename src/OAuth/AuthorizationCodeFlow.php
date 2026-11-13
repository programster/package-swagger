<?php

namespace Programster\Swagger\OAuth;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * The OAuth authorization code flow, in which the user authorizes the client, which then exchanges the
 * code it is given for an access token.
 * https://swagger.io/specification/v3/#oauth-flow-object
 */
final readonly class AuthorizationCodeFlow implements \JsonSerializable
{
    private string $m_authorizationUrl;
    private string $m_tokenUrl;
    private ScopeMap $m_scopes;
    private ?string $m_refreshUrl;
    private ExtensionMap $m_extensions;


    /**
     * @param string $authorizationUrl - the URL to send the user to, to authorize the client.
     * @param string $tokenUrl - the URL to get an access token from.
     * @param ScopeMap $scopes - the scopes that the flow offers. It may be empty.
     * @param string|null $refreshUrl - the URL to get a new access token from, if the flow supports it.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the flow.
     * @throws InvalidValueException - if any of the URLs isn't a URL.
     */
    public function __construct(
        string $authorizationUrl,
        string $tokenUrl,
        ScopeMap $scopes,
        ?string $refreshUrl = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertUrl($authorizationUrl, "The authorization URL");
        Validator::assertUrl($tokenUrl, "The token URL");

        if ($refreshUrl !== null)
        {
            Validator::assertUrl($refreshUrl, "The refresh URL");
        }

        $this->m_authorizationUrl = $authorizationUrl;
        $this->m_tokenUrl = $tokenUrl;
        $this->m_scopes = clone $scopes;
        $this->m_refreshUrl = $refreshUrl;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'authorizationUrl' => $this->m_authorizationUrl,
            'tokenUrl' => $this->m_tokenUrl,
            'refreshUrl' => $this->m_refreshUrl,
            'scopes' => $this->m_scopes,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getScopes() : ScopeMap { return clone $this->m_scopes; }
}
