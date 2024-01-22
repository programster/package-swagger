<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#integer
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaBool implements SchemaInterface, \JsonSerializable
{
    public function __construct(
        private readonly ?string $description = null,
        private readonly ?bool $default = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('type' => Type::BOOL);

        if ($this->description !== null)        { $arrayForm['description']         = $this->description; }
        if ($this->default !== null)            { $arrayForm['default']             = $this->default; }

        return $arrayForm;
    }
}
