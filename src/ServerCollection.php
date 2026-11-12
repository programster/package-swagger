<?php

/*
 * A collection that can only take Servers
 */

namespace Programster\Swagger;


final class ServerCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Server ...$servers)
    {
        parent::__construct($servers);
    }


    /**
     * @throws \Exception - if the value isn't a Server.
     */
    public function append(mixed $value) : void
    {
        if ($value instanceof Server)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Server to a " . __CLASS__);
        }
    }


    /**
     * @throws \Exception - if the value isn't a Server.
     */
    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Server)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Server value to a " . __CLASS__);
        }
    }


    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
