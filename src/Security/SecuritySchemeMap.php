<?php

namespace Programster\Swagger\Security;



class SecuritySchemeMap implements \JsonSerializable
{
    private readonly array $m_map;


    /**
     * A mapping of unique keys/names to the various security schemes.
     * This goes in the Components object for security.
     * This is not to be confused with a Security Scheme Collection that
     * @param NamedSecurityScheme ...$namedSecuritySchemes
     * @throws \Exception - if provided two or more named security schemes that have the same name.
     */
    public function __construct(NamedSecurityScheme ...$namedSecuritySchemes)
    {
        $map = [];

        foreach ($namedSecuritySchemes as $namedSecurityScheme)
        {
            /* @var $namedSecurityScheme NamedSecurityScheme */
            if (isset($map[$namedSecurityScheme->getName()]))
            {
                $msg = "Cannot have two security schemes with the same name: {$namedSecurityScheme->getName()}";
                throw new \Exception($msg);
            }

            $map[$namedSecurityScheme->getName()] = $namedSecurityScheme->getSecurityScheme();
        }

        $this->m_map = $map;
    }


    public function toArray() : array
    {
        return $this->m_map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
