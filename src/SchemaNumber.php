<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#integer
 */

namespace Programster\Swagger;


use JetBrains\PhpStorm\Internal\TentativeType;

class SchemaNumber implements \JsonSerializable
{
    public function __construct(
        private readonly ?bool $required = null,
        private readonly float|int|null $minimum = null,
        private readonly float|int|null $maximum = null,
        private readonly float|int|null $multipleOf = null,
        private readonly float|int|null $exclusiveMaximum = null,
        private readonly float|int|null $exclusiveMinimum = null,
        private readonly float|int|null $default = null
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = ['type' => Type::NUMBER];

        if ($this->required !== null) { $arrayForm = ['required' => $this->required]; }
        if ($this->minimum !== null) { $arrayForm = ['minimum' => $this->minimum]; }
        if ($this->maximum !== null) { $arrayForm = ['maximum' => $this->maximum]; }
        if ($this->multipleOf !== null) { $arrayForm = ['multipleOf' => $this->multipleOf]; }
        if ($this->exclusiveMinimum !== null) { $arrayForm = ['exclusiveMinimum' => $this->exclusiveMinimum]; }
        if ($this->exclusiveMaximum !== null) { $arrayForm = ['exclusiveMaximum' => $this->exclusiveMaximum]; }
        if ($this->default !== null) { $arrayForm = ['default' => $this->default]; }

        return $arrayForm;
    }
}
