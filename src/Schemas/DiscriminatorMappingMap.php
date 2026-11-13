<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * A discriminator's mappings, each under the value of the property that it is for.
 */
final class DiscriminatorMappingMap extends AbstractKeyedCollection
{
    /**
     * @param DiscriminatorMapping ...$mappings - the mappings.
     * @throws DuplicateKeyException - if two of them are for the same value.
     */
    public function __construct(DiscriminatorMapping ...$mappings)
    {
        parent::__construct($mappings);
    }


    public function getType() : string
    {
        return DiscriminatorMapping::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    /**
     * Get the references that the values are mapped to, e.g. "#/components/schemas/Dog".
     * @return string[]
     */
    public function getReferences() : array
    {
        $references = [];

        foreach ($this->data as $mapping)
        {
            /** @var DiscriminatorMapping $mapping */
            $references[] = $mapping->getSchema()->getReference();
        }

        return $references;
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var DiscriminatorMapping $item */
        return $item->getValue();
    }


    protected function describeKey(string $key) : string
    {
        return "a mapping for \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var DiscriminatorMapping $item */
        return $item->getSchema()->getReference();
    }
}
