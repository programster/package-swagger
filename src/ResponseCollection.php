<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The responses that an operation can give, each under its response code. There must be at least one.
 */
final class ResponseCollection extends AbstractKeyedCollection
{
    /**
     * @param MappedResponse $first - the first response. There must be at least one.
     * @param MappedResponse ...$others - any others.
     * @throws DuplicateKeyException - if two of them have the same response code.
     */
    public function __construct(MappedResponse $first, MappedResponse ...$others)
    {
        parent::__construct([$first, ...$others]);
    }


    public function getType() : string
    {
        return MappedResponse::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var MappedResponse $item */
        return $item->getCode();
    }


    protected function describeKey(string $key) : string
    {
        return "a response for the {$key} code";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var MappedResponse $item */
        return $item->getResponse();
    }
}
