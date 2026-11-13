<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Ramsey\Collection\AbstractCollection;


/**
 * The parameters of an operation, or of every operation on a path. There can't be two in the same location with the
 * same name.
 */
final class ParameterCollection extends AbstractCollection implements \JsonSerializable
{
    /**
     * @param ParameterInterface ...$parameters - the parameters, or references to them.
     * @throws DuplicateKeyException - if two of the parameters are in the same location, with the same name.
     */
    public function __construct(ParameterInterface ...$parameters)
    {
        parent::__construct($parameters);
    }


    /**
     * Set a parameter, checking that there isn't already one in the same location, with the same name.
     *
     * @param mixed $offset - the offset to set the parameter at, or null to add it to the end.
     *
     * @param mixed $value - the parameter.
     *
     * @return void
     *
     * @throws DuplicateKeyException - if there is already a parameter in the same location, with the same name.
     * @throws \Ramsey\Collection\Exception\InvalidArgumentException - if the value isn't a ParameterInterface.
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        if ($value instanceof Parameter)
        {
            foreach ($this->data as $existingOffset => $existing)
            {
                $isSame = (
                       $existingOffset !== $offset
                    && $existing instanceof Parameter
                    && $existing->getName() === $value->getName()
                    && $existing->getLocation() === $value->getLocation()
                );

                if ($isSame)
                {
                    $location = $value->getLocation()->value;
                    $message = "ParameterCollection already has a {$location} parameter named \"{$value->getName()}\".";
                    throw new DuplicateKeyException($message);
                }
            }
        }

        parent::offsetSet($offset, $value);
    }


    public function getType() : string
    {
        return ParameterInterface::class;
    }


    /**
     * Get the names of the parameters that are in the path. References aren't included, as what they refer to can't
     * be known here.
     * @return string[]
     */
    public function getPathParameterNames() : array
    {
        $names = [];

        foreach ($this->data as $parameter)
        {
            if ($parameter instanceof Parameter && $parameter->getLocation() === ParameterLocation::PATH)
            {
                $names[] = $parameter->getName();
            }
        }

        return $names;
    }


    /**
     * Check whether any of the parameters are references, which could be to parameters in the path.
     * @return bool
     */
    public function hasReferences() : bool
    {
        $hasReferences = false;

        foreach ($this->data as $parameter)
        {
            if (!($parameter instanceof Parameter))
            {
                $hasReferences = true;
            }
        }

        return $hasReferences;
    }


    public function jsonSerialize() : array
    {
        return array_values($this->data);
    }
}
