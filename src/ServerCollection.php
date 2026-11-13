<?php

namespace Programster\Swagger;

use Ramsey\Collection\AbstractCollection;


/**
 * The servers that the API (or a path, or an operation) is available on.
 */
final class ServerCollection extends AbstractCollection implements \JsonSerializable
{
    public function __construct(Server ...$servers)
    {
        parent::__construct($servers);
    }


    public function getType() : string
    {
        return Server::class;
    }


    public function jsonSerialize() : array
    {
        return array_values($this->data);
    }
}
