<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class SchemaMap implements \JsonSerializable
{
    private array $m_map;


    /**
     * @param array|null $map
     * @throws \Exception - if any of the keys in the map are duplicated, which should be impossible.
     */
    public function __construct(array $map = null)
    {
        if ($map === null)
        {
            $this->m_map = [];
        }
        else
        {
            $this->m_map = [];

            foreach ($map as $name => $schemaOrReference)
            {
                $this->add($name, $schemaOrReference);
            }
        }
    }


    public function add(string $key, SchemaObject | SchemaString | SchemaInt | SchemaArray | SchemaNumber | Reference $headerOrReference)
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
