<?php

namespace Programster\Swagger\OAuth;


class OauthFlow implements \JsonSerializable
{
    private readonly string $m_authorizationUrl;
    private readonly string $m_tokenUrl;
    private readonly ?string $m_refreshUrl;
    private readonly string $m_scopes;


    public function __construct(string $authorizationUrl, string $tokenUrl, ?string $refreshUrl, string $scopes)
    {
        $this->m_authorizationUrl = $authorizationUrl;
        $this->m_tokenUrl = $tokenUrl;
        $this->m_refreshUrl = $refreshUrl;
        $this->m_scopes = $scopes;
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [
            'authorizationUrl' => $this->m_authorizationUrl,
            'tokenUrl' => $this->m_tokenUrl,
            'scopes' => $this->m_scopes,
        ];

        if ($this->m_refreshUrl !== null)
        {
            $arrayForm['refreshUrl'] = $this->m_refreshUrl;
        }

        return $arrayForm;
    }
}
