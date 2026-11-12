<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Reference;

class NamedSchema
{
    private readonly string $m_name;
    private readonly SchemaInterface|Reference $m_schema;


    public function __construct(string $name, SchemaInterface|Reference $schema)
    {
        $this->m_name = $name;
        $this->m_schema = $schema;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getSchema() : SchemaInterface|Reference { return $this->m_schema; }
}
