<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The responses in the components, each under its name. An operation's responses, which are under their response
 * codes, are a ResponseCollection instead.
 */
final class ResponseMap extends AbstractKeyedCollection
{
    /**
     * @param NamedResponse ...$responses - the responses.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedResponse ...$responses)
    {
        parent::__construct($responses);
    }


    public function getType() : string
    {
        return NamedResponse::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedResponse $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a response named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedResponse $item */
        return $item->getResponse();
    }
}
