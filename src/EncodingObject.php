<?php

/*
 * https://swagger.io/specification/#encoding-object
 */

namespace Programster\Swagger;

class EncodingObject
{
    public function __construct(
        MimeType $contentType,
        array    $headers,
        string   $style,
        bool     $explode,
        bool     $allowReserved
    )
    {

    }
}
