<?php

/*
 * A security object for the swagger doc.
 * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#security-scheme-object
 */

namespace Programster\Swagger\Security;


final class JwtBearer implements InterfaceSecurityScheme, \JsonSerializable
{
    public function __construct(){}


    public function jsonSerialize() : mixed
    {
        return [
            'type' => "http",
            'scheme' => "bearer",
            'bearerFormat' => "JWT"
        ];
    }
}

