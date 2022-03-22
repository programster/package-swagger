<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;

final class ParameterCollection extends \ArrayObject
{
    public function __construct(Parameter | Reference ...$parameters)
    {
        parent::__construct($parameters);
    }


    public function append(mixed $value) : void
    {
        if ($value instanceof Parameter || $value instanceof Reference)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Parameter to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Parameter || $newval instanceof Reference)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Parameter value to a " . __CLASS__);
        }
    }
}
