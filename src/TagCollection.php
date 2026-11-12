<?php

namespace Programster\Swagger;


class TagCollection implements \JsonSerializable
{
    private readonly array $m_tags;


    public function __construct(Tag ...$tags)
    {
        $this->m_tags = $tags;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_tags;
    }
}
