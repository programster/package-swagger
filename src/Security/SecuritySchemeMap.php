<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\DuplicateKeyException;
use Programster\Swagger\Internal\AbstractKeyedCollection;


/**
 * The security schemes in the components, each under its name. Security requirements can only use the schemes
 * that are in here.
 */
final class SecuritySchemeMap extends AbstractKeyedCollection
{
    /**
     * @param NamedSecurityScheme ...$schemes - the security schemes.
     * @throws DuplicateKeyException - if two of them have the same name.
     */
    public function __construct(NamedSecurityScheme ...$schemes)
    {
        parent::__construct($schemes);
    }


    public function getType() : string
    {
        return NamedSecurityScheme::class;
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->toJsonObject();
    }


    protected function getKeyOf(mixed $item) : string
    {
        /** @var NamedSecurityScheme $item */
        return $item->getName();
    }


    protected function describeKey(string $key) : string
    {
        return "a security scheme named \"{$key}\"";
    }


    protected function getJsonValueOf(mixed $item) : mixed
    {
        /** @var NamedSecurityScheme $item */
        return $item->getSecurityScheme();
    }
}
