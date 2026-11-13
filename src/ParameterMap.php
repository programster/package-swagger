<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The parameters in the components, each under its name.
 */
final class ParameterMap extends AbstractKeyedCollection
{
    /**
     * @param NamedParameter ...$parameters - the parameters.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedParameter ...$parameters)
    {
        parent::__construct($parameters);
    }


    public function getType() : string
    {
        return NamedParameter::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedParameter $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a parameter named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedParameter $item */
        return $item->getParameter();
    }
}
