<?php

/* 
 * A collection that can only take Response objects
 */

namespace Programster\Swagger;

final class ResponseCollection extends \ArrayObject
{
    public function __construct(Response ...$responses)
    {
        parent::__construct($responses);
    }


    public function append($value) 
    {
        if ($value instanceof Response)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Response object to a " . __CLASS__);
        }
    }


    public function offsetSet($index, $newval) 
    {
        if ($newval instanceof Response)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Response value to a " . __CLASS__);
        }
    }
}