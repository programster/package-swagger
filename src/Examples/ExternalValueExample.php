<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ExternalValueExample implements \JsonSerializable
{
    public function __construct(
        private readonly string $externalValue
    )
    {
        if (filter_var($this->externalValue, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("externalValue needs to be a valid URL.");
        }
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    public function toArray() : array
    {
        $arrayForm = array('externalValue' => $this->externalValue);
        return $arrayForm;
    }
}
