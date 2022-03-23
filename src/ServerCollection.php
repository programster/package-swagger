<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

final class ServerCollection extends \ArrayObject implements \JsonSerializable
{
    public function __construct(Server ...$servers)
    {
        parent::__construct($servers);
    }


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

    public function jsonSerialize(): mixed
    {
        return $this->getArrayCopy();
    }
}
