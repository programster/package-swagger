<?php

namespace Programster\Swagger\OAuth;


class OauthFlow implements \JsonSerializable
{
    public function __construct(
        private readonly string $authorizationUrl,
        private readonly string $tokenUrl,
        private readonly ?string $refreshUrl,
        private readonly string $scopes,
    )
    {

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
