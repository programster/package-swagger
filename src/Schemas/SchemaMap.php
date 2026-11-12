<?php

namespace Programster\Swagger\Schemas;


class SchemaMap implements \JsonSerializable
{
    private readonly array $map;


    /**
     * @param array|null $map
     * @throws \Exception - if any of the keys in the map are duplicated, which should be impossible.
     */
    public function __construct(NamedSchema ...$schemas)
    {
        $map = [];

        foreach ($schemas as $schema)
        {
            /* @var $schema NamedSchema */
            if (isset($map[$schema->getName()]))
            {
                throw new \Exception("{$schema->getName()} already taken in " . __CLASS__);
            }

            $map[$schema->getName()] = $schema->getSchema();
        }

        $this->map = $map;
    }


    public function jsonSerialize(): array
    {
        return $this->map;
    }
}
