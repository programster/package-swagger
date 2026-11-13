<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The content of a request body, response, parameter or header: a media type for each mime type it can be sent
 * as, under that mime type. There must be at least one.
 */
final class ContentMap extends AbstractKeyedCollection
{
    /**
     * @param MediaType $first - the first media type. There must be at least one.
     * @param MediaType ...$others - any others.
     * @throws DuplicateKeyException - if two of them have the same mime type.
     */
    public function __construct(MediaType $first, MediaType ...$others)
    {
        parent::__construct([$first, ...$others]);
    }


    public function getType() : string
    {
        return MediaType::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var MediaType $item */
        return $item->getMimeType();
    }


    protected function describeKey(string $key) : string
    {
        return "a media type for \"{$key}\"";
    }
}
