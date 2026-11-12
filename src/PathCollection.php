<?php

/*
 * A collection that can only take Paths
 */

namespace Programster\Swagger;


final class PathCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Path ...$paths)
    {
        parent::__construct($paths);
    }


    /**
     * Append a path to this array object.
     * @param mixed $value - the path to append.
     * @return void
     * @throws \Exception - if the value passed is not an instance of a Path
     */
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


    /**
     * Override a specific path.
     * @param mixed $index - the index to set the path at.
     * @param mixed $newval - the path to set.
     * @return void
     * @throws \Exception - if the new value passed is not of a Path type.
     */
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


    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
