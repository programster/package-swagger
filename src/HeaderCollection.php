<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The headers of a response or an encoding, each under its HTTP name. The headers in the components, which are under
 * their component names, are a HeaderMap instead.
 */
final class HeaderCollection extends AbstractKeyedCollection
{
    /**
     * @param MappedHeader ...$headers - the headers.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(MappedHeader ...$headers)
    {
        parent::__construct($headers);
    }


    public function getType() : string
    {
        return MappedHeader::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var MappedHeader $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a header named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var MappedHeader $item */
        return $item->getHeader();
    }
}
