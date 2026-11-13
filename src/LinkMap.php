<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * A map of named links, for a response or the components.
 */
final class LinkMap extends AbstractKeyedCollection
{
    /**
     * @param NamedLink ...$links - the links.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedLink ...$links)
    {
        parent::__construct($links);
    }


    public function getType() : string
    {
        return NamedLink::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedLink $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a link named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedLink $item */
        return $item->getLink();
    }
}
