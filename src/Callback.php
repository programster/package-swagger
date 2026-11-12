<?php

namespace Programster\Swagger;


class Callback implements \JsonSerializable
{
    private readonly string $m_expression;
    private readonly Path $m_pathItem;


    public function __construct(string $expression, Path $pathItem)
    {
        $this->m_expression = $expression;
        $this->m_pathItem = $pathItem;
    }


    public function jsonSerialize(): mixed
    {
        return [$this->m_expression => $this->m_pathItem];
    }
}
