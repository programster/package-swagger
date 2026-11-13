<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The request bodies in the components, each under its name.
 */
final class RequestBodyMap extends AbstractKeyedCollection
{
    /**
     * @param NamedRequestBody ...$requestBodies - the request bodies.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedRequestBody ...$requestBodies)
    {
        parent::__construct($requestBodies);
    }


    public function getType() : string
    {
        return NamedRequestBody::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedRequestBody $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a request body named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedRequestBody $item */
        return $item->getRequestBody();
    }
}
