<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#integer
 */

namespace Programster\Swagger\Schemas;


class SchemaInt implements SchemaInterface, \JsonSerializable
{
    public function __construct(
        private readonly ?string $description = null,
        private readonly ?bool $required = null,
        private readonly int|null $minimum = null,
        private readonly int|null $maximum = null,
        private readonly int|null $multipleOf = null,
        private readonly int|null $exclusiveMaximum = null,
        private readonly int|null $exclusiveMinimum = null,
        private readonly ?int $default = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('type' => Type::INT);

        if ($this->required !== null)           { $arrayForm['required']            = $this->required; }
        if ($this->description !== null)        { $arrayForm['description']         = $this->description; }
        if ($this->minimum !== null)            { $arrayForm['minimum']             = $this->minimum; }
        if ($this->maximum !== null)            { $arrayForm['maximum']             = $this->maximum; }
        if ($this->multipleOf !== null)         { $arrayForm['multipleOf']          = $this->multipleOf; }
        if ($this->exclusiveMinimum !== null)   { $arrayForm['exclusiveMinimum']    = $this->exclusiveMinimum; }
        if ($this->exclusiveMaximum !== null)   { $arrayForm['exclusiveMaximum']    = $this->exclusiveMaximum; }
        if ($this->default !== null)            { $arrayForm['default']             = $this->default; }

        return $arrayForm;
    }
}
