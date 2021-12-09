<?php

/*
 * A collection that can only take Paths
 */

namespace Programster\Swagger;

final class DefinitionCollection extends \ArrayObject
{
    public function __construct(Definition ...$definitions)
    {
        parent::__construct($definitions);
    }


    public function append(mixed $value) : void
    {
        if ($value instanceof Definition)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Definition to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Definition)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Definition value to a " . __CLASS__);
        }
    }
}