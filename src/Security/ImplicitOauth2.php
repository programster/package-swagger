<?php

/*
 * A security object for the swagger doc.
 * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#security-scheme-object
 */

namespace Programster\Swagger\Security;

use Programster\Swagger\OAuth\OauthFlows;


final class ImplicitOauth2 implements InterfaceSecurityScheme, \JsonSerializable
{
    public function __construct(private readonly OauthFlows $flows)
    {

    }


    public function jsonSerialize(): mixed
    {
        return [
            'type' => "oauth2",
            'flows' => $this->flows,
        ];
    }
}

