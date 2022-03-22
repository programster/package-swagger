<?php

namespace Programster\Swagger\OAuth;


class AuthorizationCodeFlow implements \JsonSerializable
{
    public function __construct(
        private readonly string $authorizationUrl,
        private readonly string $tokenUrl,
        private readonly ScopeMap $scopes,
        private readonly ?string $refreshUrl = null,
    )
    {
        if (filter_var($authorizationUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$authorizationUrl} is not a valid URL");
        }

        if (filter_var($tokenUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$tokenUrl} is not a valid URL");
        }

        if ($refreshUrl !== null && filter_var($refreshUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$refreshUrl} is not a valid URL");
        }
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [
            'authorizationUrl' => $this->authorizationUrl,
            'tokenUrl' => $this->tokenUrl,
            'scopes' => $this->scopes,
        ];

        if ($this->refreshUrl !== null)
        {
            $arrayForm['refreshUrl'] = $this->refreshUrl;
        }

        return $arrayForm;
    }
}
