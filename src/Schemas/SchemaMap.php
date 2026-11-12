<?php

namespace Programster\Swagger\Schemas;


class SchemaMap implements \JsonSerializable
{
    private readonly array $m_map;


    /**
     * @param NamedSchema ...$schemas - the schemas, each of which must have a unique name.
     * @throws \Exception - if two of the schemas have the same name.
     */
    public function __construct(NamedSchema ...$schemas)
    {
        $map = [];

        foreach ($schemas as $schema)
        {
            if (isset($map[$schema->getName()]))
            {
                throw new \Exception("{$schema->getName()} already taken in " . __CLASS__);
            }

            $map[$schema->getName()] = $schema->getSchema();
        }

        $this->m_map = $map;
    }


    public function jsonSerialize(): array
    {
        return $this->m_map;
    }
}
