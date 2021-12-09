<?php

/*
 * A collection that can only take Paths
 */

namespace Programster\Swagger;

final class PathCollection extends \ArrayObject
{
    public function __construct(Path ...$paths)
    {
        parent::__construct($paths);
    }


    public function append(mixed $value) : void
    {
        if ($value instanceof Path)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Path to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Path)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Path value to a " . __CLASS__);
        }
    }
}