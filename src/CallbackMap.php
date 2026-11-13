<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * A map of named callbacks, for an operation or the components.
 */
final class CallbackMap extends AbstractKeyedCollection
{
    /**
     * @param NamedCallback ...$callbacks - the callbacks.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedCallback ...$callbacks)
    {
        parent::__construct($callbacks);
    }


    public function getType() : string
    {
        return NamedCallback::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedCallback $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a callback named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedCallback $item */
        return $item->getCallback();
    }
}
