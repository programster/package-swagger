<?php

namespace Programster\Swagger\References;

use Programster\Swagger\Schemas\SchemaInterface;


/**
 * A reference to a schema, which can be used wherever one can.
 */
final readonly class SchemaReference extends ComponentReference implements SchemaInterface
{
    protected static function getComponentSection() : string
    {
        return "schemas";
    }
}
