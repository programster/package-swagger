<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The schemes that a security requirement needs, each under the scheme's name.
 *
 * @internal - this is used by the package itself, and isn't part of its public API.
 */
final class RequiredSchemeCollection extends AbstractKeyedCollection
{
    /**
     * @param RequiredScheme ...$schemes - the required schemes.
     * @throws DuplicateKeyException - if two of them have the same scheme.
     */
    public function __construct(RequiredScheme ...$schemes)
    {
        parent::__construct($schemes);
    }


    public function getType() : string
    {
        return RequiredScheme::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var RequiredScheme $item */
        return $item->getSchemeName();
    }


    protected function describeKey(string $key) : string
    {
        return "the \"{$key}\" security scheme";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var RequiredScheme $item */
        return $item->getScopes();
    }
}
