<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\SchemaReference;


/**
 * A schema with a name, to put in the components (in a SchemaMap), so that it can be referenced from elsewhere.
 */
final readonly class NamedSchema
{
    private string $m_name;
    private SchemaInterface $m_schema;


    /**
     * @param string $name - the name of the schema, e.g. "User". It can only contain letters, numbers, dots, dashes
     * and underscores.
     *
     * @param SchemaInterface $schema - the schema.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, SchemaInterface $schema)
    {
        Validator::assertComponentName($name, "The schema's name");
        $this->m_name = $name;
        $this->m_schema = $schema;
    }


    /**
     * Create a reference to this schema, for using it elsewhere, once it is in the components.
     * @return SchemaReference
     */
    public function createReference() : SchemaReference
    {
        return SchemaReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getSchema() : SchemaInterface { return $this->m_schema; }
}
