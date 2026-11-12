<?php

/*
 * A collection that can only take Operations
 */

namespace Programster\Swagger;


final class OperationCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Operation ...$operations)
    {
        parent::__construct($operations);
    }


    /**
     * @throws \Exception - if the value isn't an Operation.
     */
    public function append(mixed $value) : void
    {
        if ($value instanceof Operation)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Operation to a " . __CLASS__);
        }
    }


    /**
     * @throws \Exception - if the value isn't an Operation.
     */
    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Operation)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Operation value to a " . __CLASS__);
        }
    }


    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
