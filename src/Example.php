<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Example implements \JsonSerializable
{
    public function __construct(
        private readonly ?string $summary,
        private readonly ?string $description,
        private readonly mixed $value,
        private readonly ?string $externalValue,
    )
    {
        if (
               $this->summary === null
            && $this->description === null
            && $this->value === null
            && $this->externalValue === null
        )
        {
            throw new \Exception("Must provide at least one property when constructing an " . __CLASS__);
        }
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [];

        if ($this->summary !== null) { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->value !== null) { $arrayForm['value'] = $this->value; }
        if ($this->externalValue !== null) { $arrayForm['externalValue'] = $this->externalValue; }

        return $arrayForm;
    }
}
