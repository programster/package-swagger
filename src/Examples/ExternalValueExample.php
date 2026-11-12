<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ExternalValueExample implements \JsonSerializable
{
    private readonly string $m_externalValue;


    /**
     * @param string $externalValue - the URL of the example.
     * @throws \Exception - if $externalValue isn't a valid URL.
     */
    public function __construct(string $externalValue)
    {
        if (filter_var($externalValue, FILTER_VALIDATE_URL) === false)
        {
            throw new \Exception("externalValue needs to be a valid URL.");
        }

        $this->m_externalValue = $externalValue;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    public function toArray() : array
    {
        return ['externalValue' => $this->m_externalValue];
    }
}
