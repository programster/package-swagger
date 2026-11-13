<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The headers in the components, each under its component name. The headers of a response or an encoding, which are
 * under their HTTP names, are a HeaderCollection instead.
 */
final class HeaderMap extends AbstractKeyedCollection
{
    /**
     * @param NamedHeader ...$headers - the headers.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedHeader ...$headers)
    {
        parent::__construct($headers);
    }


    public function getType() : string
    {
        return NamedHeader::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedHeader $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a header named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedHeader $item */
        return $item->getHeader();
    }
}
