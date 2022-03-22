<?php

namespace Programster\Swagger\OAuth;

use Programster\Swagger\ScopeMap;

class ImplicitFlow implements \JsonSerializable
{
    public function __construct(
        private readonly string $authorizationUrl,
        private readonly ScopeMap $scopes,
        private readonly ?string $refreshUrl = null,
    )
    {
        if (filter_var($authorizationUrl, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("{$authorizationUrl} is not a valid URL");
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
            'scopes' => $this->scopes,
        ];

        if ($this->refreshUrl !== null)
        {
            $arrayForm['refreshUrl'] = $this->refreshUrl;
        }

        return $arrayForm;
    }
}
