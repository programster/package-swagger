<?php

/*
 * A collection that can only take Parameters (or references to them)
 */

namespace Programster\Swagger;


final class ParameterCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Parameter|Reference ...$parameters)
    {
        parent::__construct($parameters);
    }


    /**
     * @throws \Exception - if the value isn't a Parameter or Reference.
     */
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


    /**
     * @throws \Exception - if the value isn't a Parameter or Reference.
     */
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


    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
