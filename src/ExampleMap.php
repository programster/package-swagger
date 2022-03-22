<?php

namespace Programster\Swagger;


class ExampleMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $name, Example $example)
    {
        $this->m_map = array($name => $example);
    }


    public function add(string $name, Example $example)
    {
        if (isset($this->m_map[$name]))
        {
            throw new \Exception("{$name} already taken in " . __CLASS__);
        }

        $this->m_map[$name] = $example;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $name
     * @param Header|Reference $mediaType
     * @return void
     */
    public function addOrReplace(string $name, Example $example)
    {
        $this->m_map[$name] = $example;
    }


    public function toArray(): array
    {
        return $this->m_map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}

