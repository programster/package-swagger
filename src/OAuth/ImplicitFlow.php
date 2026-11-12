<?php

namespace Programster\Swagger\OAuth;


class ImplicitFlow implements \JsonSerializable
{
    private readonly string $m_authorizationUrl;
    private readonly ScopeMap $m_scopes;
    private readonly ?string $m_refreshUrl;


    /**
     * @throws \Exception - if any of the URLs aren't valid.
     */
    public function __construct(string $authorizationUrl, ScopeMap $scopes, ?string $refreshUrl = null)
    {
        if (filter_var($authorizationUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$authorizationUrl} is not a valid URL");
        }

        if ($refreshUrl !== null && filter_var($refreshUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$refreshUrl} is not a valid URL");
        }

        $this->m_authorizationUrl = $authorizationUrl;
        $this->m_scopes = $scopes;
        $this->m_refreshUrl = $refreshUrl;
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [
            'authorizationUrl' => $this->m_authorizationUrl,
            'scopes' => $this->m_scopes,
        ];

        if ($this->m_refreshUrl !== null)
        {
            $arrayForm['refreshUrl'] = $this->m_refreshUrl;
        }

        return $arrayForm;
    }
}
