<?php

/*
 * A security object for the swagger doc.
 * https://swagger.io/specification/#security-scheme-object
 */

namespace Programster\Swagger\Security;

use Programster\Swagger\OAuth\OauthFlows;


final class ImplicitOauth2 implements InterfaceSecurityScheme, \JsonSerializable
{
    private readonly OauthFlows $m_flows;


    public function __construct(OauthFlows $flows)
    {
        $this->m_flows = $flows;
    }


    public function jsonSerialize() : array
    {
        return [
            'type' => "oauth2",
            'flows' => $this->m_flows,
        ];
    }
}
