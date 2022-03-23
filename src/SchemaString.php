<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#integer
 */

namespace Programster\Swagger;


class SchemaString implements \JsonSerializable
{
    public function __construct(
        private readonly ?string $default = null,
        private readonly int|null $minLength = null,
        private readonly int|null $maxLength = null,
        private readonly ?string $pattern = null,
        private readonly ?Format $format = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('type' => Type::STRING);

        if ($this->default !== null) { $arrayForm['default'] = $this->default; }
        if ($this->minLength !== null) { $arrayForm['minLength'] = $this->minLength; }
        if ($this->maxLength !== null) { $arrayForm['maxLength'] = $this->maxLength; }
        if ($this->pattern !== null) { $arrayForm['pattern'] = $this->pattern; }
        if ($this->format !== null) { $arrayForm['format'] = $this->format; }

        return $arrayForm;
    }
}
