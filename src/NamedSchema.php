<?php

namespace Programster\Swagger;

class NamedSchema
{
    public function __construct(private readonly string $name, private readonly SchemaInterface | Reference $schema)
    {

    }

    public function getName() : string { return $this->name; }
    public function getSchema() : SchemaInterface | Reference { return $this->schema; }
}
