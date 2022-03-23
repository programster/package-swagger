<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class TagCollection implements \JsonSerializable
{
    private readonly array $tags;

    public function __construct(Tag ...$tags)
    {
        $this->tags = $tags;
    }


    public function jsonSerialize(): mixed
    {
        return $this->tags;
    }
}
