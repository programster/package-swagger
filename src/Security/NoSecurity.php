<?php

/*
 * A security type for "public" (which can't be used as a class name because it is a keyword). This allows one
 * to override the top-level security scheme, in order to expose certain routes/operations as public.
 */

namespace Programster\Swagger\Security;


class NoSecurity implements InterfaceSecurityScheme, \JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return [];
    }
}
