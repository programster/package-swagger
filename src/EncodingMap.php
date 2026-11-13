<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * How to encode the properties of a multipart or form request body, under the name of the property each is for.
 */
final class EncodingMap extends AbstractKeyedCollection
{
    /**
     * @param Encoding ...$encodings - the encodings.
     * @throws DuplicateKeyException - if two of them have the same property name.
     */
    public function __construct(Encoding ...$encodings)
    {
        parent::__construct($encodings);
    }


    public function getType() : string
    {
        return Encoding::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Encoding $item */
        return $item->getPropertyName();
    }


    protected function describeKey(string $key) : string
    {
        return "an encoding for the \"{$key}\" property";
    }
}
