<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The tags in the document. Each must have a unique name.
 */
final class TagCollection extends AbstractKeyedCollection
{
    /**
     * @param Tag ...$tags - the tags.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(Tag ...$tags)
    {
        parent::__construct($tags);
    }


    public function getType() : string
    {
        return Tag::class;
    }


    public function jsonSerialize() : array
    {
        return array_values($this->data);
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Tag $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a tag named \"{$key}\"";
    }
}
