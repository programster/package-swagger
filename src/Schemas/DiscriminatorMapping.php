<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\SchemaReference;


/**
 * A value of a discriminator's property, and the schema that a value with it matches, e.g. "dog" and the Dog schema.
 * These go in a Discriminator's DiscriminatorMappingMap.
 */
final readonly class DiscriminatorMapping
{
    private string $m_value;
    private SchemaReference $m_schema;


    /**
     * @param string $value - the value of the discriminator's property, e.g. "dog".
     * @param SchemaReference $schema - the schema that a value with it matches, e.g. $dog->createReference().
     * @throws InvalidValueException - if the value is empty.
     */
    public function __construct(string $value, SchemaReference $schema)
    {
        Validator::assertNotEmpty($value, "The discriminator mapping's value");
        $this->m_value = $value;
        $this->m_schema = $schema;
    }


    # Accessors
    public function getValue() : string { return $this->m_value; }
    public function getSchema() : SchemaReference { return $this->m_schema; }
}
