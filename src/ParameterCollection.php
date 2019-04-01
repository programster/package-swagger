<?php

/* 
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;

final class ParameterCollection extends \ArrayObject
{
    public function __construct(Parameter ...$parameters)
    {
        parent::__construct($parameters);
    }


    public function append($value) 
    {
        if ($value instanceof Parameter)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Parameter to a " . __CLASS__);
        }
    }


    public function offsetSet($index, $newval) 
    {
        if ($newval instanceof Parameter)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Parameter value to a " . __CLASS__);
        }
    }
}