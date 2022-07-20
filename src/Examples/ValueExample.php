<?php

/*
 * https://swagger.io/specification/#example-object
 */

namespace Programster\Swagger\Examples;


class ValueExample implements \JsonSerializable
{
    public function __construct(
        private readonly string|int|float|bool|array $value
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        return $this->value;
    }


    # Accessors
    public function getValue() : string|int|float|bool|array { return $this->value; }
}
