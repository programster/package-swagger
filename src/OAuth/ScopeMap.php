<?php

namespace Programster\Swagger\OAuth;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The scopes that an OAuth flow offers, each under its name. It may be empty.
 */
final class ScopeMap extends AbstractKeyedCollection
{
    /**
     * @param Scope ...$scopes - the scopes.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(Scope ...$scopes)
    {
        parent::__construct($scopes);
    }


    public function getType() : string
    {
        return Scope::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var Scope $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a scope named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var Scope $item */
        return $item->getDescription();
    }
}
