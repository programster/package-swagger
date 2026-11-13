<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The variables in a server's URL, each under its name.
 */
final class ServerVariableMap extends AbstractKeyedCollection
{
    /**
     * @param ServerVariable ...$variables - the variables.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(ServerVariable ...$variables)
    {
        parent::__construct($variables);
    }


    public function getType() : string
    {
        return ServerVariable::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var ServerVariable $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a variable named \"{$key}\"";
    }
}
