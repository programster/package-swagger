<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;


final class OperationCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Operation ...$operations)
    {
        parent::__construct($operations);
    }


    public function append($value)
    {
        if ($value instanceof Operation)
        {
            parent::append($value);
        }
        else
        {
            throw new Exception("Cannot append non Operation to a " . __CLASS__);
        }
    }


    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Operation)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new Exception("Cannot add a non Operation value to a " . __CLASS__);
        }
    }


    public function jsonSerialize(): mixed
    {
        return $this->getArrayCopy();
    }
}
