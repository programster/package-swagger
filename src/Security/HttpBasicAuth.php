<?php

/*
 * A security object for the swagger doc.
 * https://swagger.io/specification/#security-scheme-object
 */

namespace Programster\Swagger\Security;


final class HttpBasicAuth implements InterfaceSecurityScheme, \JsonSerializable
{
    public function __construct()
    {
        // nothing
    }

    public function jsonSerialize() : mixed
    {
        return [
            'type' => 'http',
            'scheme' => "basic"
        ];
    }
}

