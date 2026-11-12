<?php

namespace Programster\Swagger\OAuth;


class OauthFlows implements \JsonSerializable
{
    private readonly ?ImplicitFlow $m_implicit;
    private readonly ?PasswordFlow $m_password;
    private readonly ?ClientCredentialsFlow $m_clientCredentials;
    private readonly ?AuthorizationCodeFlow $m_authorizationCode;


    /**
     * @throws \Exception - if none of the flows are provided.
     */
    public function __construct(
        ?ImplicitFlow $implicit = null,
        ?PasswordFlow $password = null,
        ?ClientCredentialsFlow $clientCredentials = null,
        ?AuthorizationCodeFlow $authorizationCode = null,
    )
    {
        if (
               $implicit === null
            && $password === null
            && $clientCredentials === null
            && $authorizationCode === null
        )
        {
            throw new \Exception("Must provide at least one of the types of flows.");
        }

        $this->m_implicit = $implicit;
        $this->m_password = $password;
        $this->m_clientCredentials = $clientCredentials;
        $this->m_authorizationCode = $authorizationCode;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'implicit' => $this->m_implicit,
            'password' => $this->m_password,
            'clientCredentials' => $this->m_clientCredentials,
            'authorizationCode' => $this->m_authorizationCode,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_filter($optionalFields, $isProvided);
    }
}
