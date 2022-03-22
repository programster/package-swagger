<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Callback implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $expression, PathItem $pathItem)
    {
        $this->m_map = [
            $expression => $pathItem
        ];
    }


    public function add(string $expression, PathItem $pathItem)
    {
        if (isset($this->m_map[$expression]))
        {
            throw new \Exception("{$expression} already taken in " . __CLASS__);
        }

        $this->m_map[$expression] = $pathItem;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $expression
     * @param Header|Reference $pathItem
     * @return void
     */
    public function addOrReplace(string $expression, PathItem $pathItem)
    {
        $this->m_map[$expression] = $pathItem;
    }


    public function toArray() : array
    {
        return $this->m_map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
