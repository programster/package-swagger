<?php

namespace Programster\Swagger\Schemas;

use Ramsey\Collection\AbstractCollection;


/**
 * A list of schemas (or references to them), for combining them with SchemaAllOf, SchemaOneOf or SchemaAnyOf.
 */
final class SchemaCollection extends AbstractCollection implements \JsonSerializable
{
    /**
     * @param SchemaInterface ...$schemas - the schemas, or references to them.
     */
    public function __construct(SchemaInterface ...$schemas)
    {
        parent::__construct($schemas);
    }


    public function getType() : string
    {
        return SchemaInterface::class;
    }


    public function jsonSerialize() : array
    {
        return array_values($this->data);
    }
}
