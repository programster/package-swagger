<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The paths of the API. Two paths that only differ in the names of their variables (e.g. "/users/{id}" and
 * "/users/{name}") are the same path, so there can't be both.
 * https://swagger.io/specification/v3/#paths-object
 */
final class PathCollection extends AbstractKeyedCollection
{
    /**
     * @param Path ...$paths - the paths.
     * @throws DuplicateKeyException - if two of them are the same path.
     */
    public function __construct(Path ...$paths)
    {
        parent::__construct($paths);
    }


    public function getType() : string
    {
        return Path::class;
    }


    public function jsonSerialize() : \stdClass
    {
        $object = new \stdClass();

        foreach ($this->data as $path)
        {
            /** @var Path $path */
            $object->{$path->getRelativePath()} = $path->getItem();
        }

        return $object;
    }


    /**
     * Get a path's key, which has the names of its variables taken out, as they don't make it a different path.
     */
    protected function getKeyOf(mixed $item) : string
    {
        /** @var Path $item */
        return preg_replace('/\{[^}]*\}/', '{}', $item->getRelativePath());
    }


    protected function describeKey(string $key) : string
    {
        return "a path that matches \"{$key}\" (paths that only differ in the names of their variables are the same)";
    }
}
