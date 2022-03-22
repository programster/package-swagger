<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;

final class SecurityRequirementCollection extends \ArrayObject
{
    public function __construct(SecurityRequirement ...$schemes)
    {
        parent::__construct($schemes);
    }


    public function append(mixed $value) : void
    {
        if ($value instanceof SecurityRequirement)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non SecurityRequirement to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof SecurityRequirement)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non SecurityRequirement value to a " . __CLASS__);
        }
    }
}
