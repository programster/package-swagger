<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ValueExample implements \JsonSerializable
{
    private readonly string|int|float|bool|array $m_value;


    public function __construct(string|int|float|bool|array $value)
    {
        $this->m_value = $value;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_value;
    }


    # Accessors
    public function getValue() : string|int|float|bool|array { return $this->m_value; }
}
