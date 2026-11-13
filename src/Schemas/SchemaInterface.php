<?php

namespace Programster\Swagger\Schemas;


/**
 * A schema, or a reference to one (SchemaReference).
 */
interface SchemaInterface extends \JsonSerializable
{
    /**
     * Get the schema as a JSON object.
     * @return \stdClass
     */
    public function jsonSerialize() : \stdClass;
}
