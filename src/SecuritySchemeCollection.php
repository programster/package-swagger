<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;

final class SecuritySchemeCollection extends \ArrayObject
{
    public function __construct(SecurityScheme ...$schemes)
    {
        parent::__construct($schemes);
    }


    public function append(mixed $value) : void
    {
        if ($value instanceof SecurityScheme)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non SecurityScheme to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof SecurityScheme)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non SecurityScheme value to a " . __CLASS__);
        }
    }
}