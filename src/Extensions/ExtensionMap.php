<?php

namespace Programster\Swagger\Extensions;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The specification extensions of an object, each under its name, which are output as extra fields of the object.
 */
final class ExtensionMap extends AbstractKeyedCollection
{
    /**
     * @param Extension ...$extensions - the extensions.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(Extension ...$extensions)
    {
        parent::__construct($extensions);
    }


    public function getType() : string
    {
        return Extension::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Extension $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "an extension named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var Extension $item */
        return $item->getValue();
    }
}
