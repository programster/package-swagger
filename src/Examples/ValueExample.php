<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ValueExample implements \JsonSerializable
{
    public function __construct(
        private readonly string|int|float|bool $value,
        private readonly ?string $summary = null,
        private readonly ?string $description = null
    )
    {

    }

    public function jsonSerialize(): mixed
    {
        $arrayForm = ['value' => $this->value];
        if ($this->summary !== null) { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        return $arrayForm;
    }
}
