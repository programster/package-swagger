<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The schemas in the components, each under its name.
 */
final class SchemaMap extends AbstractKeyedCollection
{
    /**
     * @param NamedSchema ...$schemas - the schemas.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedSchema ...$schemas)
    {
        parent::__construct($schemas);
    }


    public function getType() : string
    {
        return NamedSchema::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedSchema $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a schema named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedSchema $item */
        return $item->getSchema();
    }
}
