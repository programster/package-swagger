<?php

/*
 * https://swagger.io/specification/#encoding-object
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Encoding implements \JsonSerializable
{
    public function __construct(
        MimeType $contentType,
        HeaderMap $headers,
        string $style,
        ?bool $explode = null,
        bool $allowReserved
    )
    {

    }

    public function jsonSerialize(): mixed
    {
        $arrayForm = [];
    }
}
