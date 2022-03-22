<?php

namespace Programster\Swagger;


class Callback implements \JsonSerializable
{
    public function __construct(
        private readonly string $expression,
        private readonly Path $pathItem
    )
    {

    }

    public function jsonSerialize(): mixed
    {
        return [$this->expression => $this->pathItem];
    }
}
