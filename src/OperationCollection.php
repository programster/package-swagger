<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The operations on a path, each under its method. There can only be one for each method.
 */
final class OperationCollection extends AbstractKeyedCollection
{
    /**
     * @param Operation ...$operations - the operations.
     * @throws DuplicateKeyException - if two of them have the same method.
     */
    public function __construct(Operation ...$operations)
    {
        parent::__construct($operations);
    }


    public function getType() : string
    {
        return Operation::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Operation $item */
        return $item->getMethod()->value;
    }


    protected function describeKey(string $key) : string
    {
        return "a " . strtoupper($key) . " operation";
    }
}
