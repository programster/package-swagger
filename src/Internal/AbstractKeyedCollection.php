<?php

namespace Programster\Swagger\Internal;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Ramsey\Collection\AbstractCollection;


/**
 * A typed collection in which every item has a key (e.g. a schema's name) that must be unique, usually because the
 * item is output under it. Adding an item whose key is already taken throws a DuplicateKeyException, whether it is
 * passed to the constructor or added later.
 *
 * @internal - this is the base class of the package's maps, and isn't meant to be extended outside of it.
 */
abstract class AbstractKeyedCollection extends AbstractCollection implements \JsonSerializable
{
    /**
     * Set an item, checking that no other item already has its key.
     *
     * @param mixed $offset - the offset to set the item at, or null to add it to the end.
     *
     * @param mixed $value - the item.
     *
     * @return void
     *
     * @throws DuplicateKeyException - if another item already has the same key.
     * @throws \Ramsey\Collection\Exception\InvalidArgumentException - if the item isn't of the collection's type.
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $type = $this->getType();

        if ($value instanceof $type)
        {
            $key = $this->getKeyOf($value);

            foreach ($this->data as $existingOffset => $existingItem)
            {
                if ($existingOffset !== $offset && $this->getKeyOf($existingItem) === $key)
                {
                    $collectionName = (new \ReflectionClass($this))->getShortName();
                    $description = $this->describeKey($key);
                    throw new DuplicateKeyException("{$collectionName} already has {$description}.");
                }
            }
        }

        parent::offsetSet($offset, $value);
    }


    /**
     * Get the keys of the items, in the order they were added.
     * @return string[]
     */
    public function getKeys() : array
    {
        $keys = [];

        foreach ($this->data as $item)
        {
            $keys[] = $this->getKeyOf($item);
        }

        return $keys;
    }


    public function hasKey(string $key) : bool
    {
        return in_array($key, $this->getKeys(), true);
    }


    /**
     * Get the key that an item is output under, which must be unique within the collection.
     * @param mixed $item - an item of the collection's type.
     * @return string
     */
    abstract protected function getKeyOf(mixed $item) : string;


    /**
     * Describe an item with a key, for the message when two have the same key, e.g. 'a schema named "User"'.
     * @param string $key - the key.
     * @return string
     */
    abstract protected function describeKey(string $key) : string;


    /**
     * Get what an item is output as, under its key. By default, this is the item itself.
     * @param mixed $item - an item of the collection's type.
     * @return mixed
     */
    protected function getJsonValueOf(mixed $item) : mixed
    {
        return $item;
    }


    /**
     * Get the items as a JSON object, with each one under its key. It is an object even when there are no items, so
     * that it is output as {} rather than [].
     * @return \stdClass
     */
    protected function toJsonObject() : \stdClass
    {
        $object = new \stdClass();

        foreach ($this->data as $item)
        {
            $object->{$this->getKeyOf($item)} = $this->getJsonValueOf($item);
        }

        return $object;
    }
}
