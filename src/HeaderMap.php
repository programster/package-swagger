<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class HeaderMap implements \JsonSerializable
{
    private readonly array $map;


    public function __construct(Header ...$headers)
    {
        $map = [];

        foreach ($headers as $header)
        {
            /* @var $header Header */
            if (isset($map[$header->getName()]))
            {
                throw new \Exception("Duplicate header: {$header->getName()}");
            }

            $map[$header->getName()] = $header;
        }

        $this->map = $map;
    }


    public function toArray() : array
    {
        return $this->map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
