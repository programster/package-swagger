<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class SchemaMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $key, Schema | Reference $headerOrReference)
    {
        $this->m_map = [
            $key => $headerOrReference
        ];
    }


    public function add(string $key, Schema | Reference $headerOrReference)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_map[$key] = $headerOrReference;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param Header|Reference $schema
     * @return void
     */
    public function addOrReplace(string $key, Schema | Reference $schema)
    {
        $this->m_map[$key] = $schema;
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
