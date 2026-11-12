<?php

/*
 * A security object for the swagger doc.
 * https://swagger.io/specification/#security-scheme-object
 */

namespace Programster\Swagger\Security;


final class JwtBearer implements InterfaceSecurityScheme, \JsonSerializable
{
    public function jsonSerialize() : array
    {
        return [
            'type' => "http",
            'scheme' => "bearer",
            'bearerFormat' => "JWT",
        ];
    }
}
