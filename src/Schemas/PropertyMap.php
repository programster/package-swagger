<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The properties of an object's schema (SchemaObject), each under its name.
 */
final class PropertyMap extends AbstractKeyedCollection
{
    /**
     * @param Property ...$properties - the properties.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(Property ...$properties)
    {
        parent::__construct($properties);
    }


    public function getType() : string
    {
        return Property::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    /**
     * Get the names of the properties that are required, in the order they were added.
     * @return string[]
     */
    public function getRequiredNames() : array
    {
        $names = [];

        foreach ($this->data as $property)
        {
            /** @var Property $property */
            if ($property->isRequired())
            {
                $names[] = $property->getName();
            }
        }

        return $names;
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Property $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a property named \"{$key}\"";
    }
}
