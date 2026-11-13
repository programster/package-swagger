<?php

namespace Programster\Swagger\Examples;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * A map of named examples, for when there is more than one example of something.
 */
final class ExampleMap extends AbstractKeyedCollection
{
    /**
     * @param NamedExample ...$examples - the examples.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedExample ...$examples)
    {
        parent::__construct($examples);
    }


    public function getType() : string
    {
        return NamedExample::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedExample $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "an example named \"{$key}\"";
    }
}
