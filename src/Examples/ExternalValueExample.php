<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ExternalValueExample implements \JsonSerializable
{
    public function __construct(
        private readonly string $externalValue,
        private readonly ?string $summary,
        private readonly ?string $description,
    )
    {
        if (filter_var($this->externalValue, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("externalValue needs to be a valid URL.");
        }
    }

    public function jsonSerialize(): mixed
    {
        $arrayForm = array('externalValue' => $this->externalValue);
        if ($this->summary !== null) { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        return $arrayForm;
    }
}
