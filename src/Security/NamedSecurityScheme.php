<?php

namespace Programster\Swagger\Security;

class NamedSecurityScheme
{
    private readonly string $m_name;
    private readonly InterfaceSecurityScheme $m_scheme;


    public function __construct(string $name, InterfaceSecurityScheme $scheme)
    {
        $this->m_name = $name;
        $this->m_scheme = $scheme;
    }


    /**
     * Create a requirement for this security scheme, to use on the whole document or an operation.
     * @param string[] $scopes - the scopes that are needed, if the scheme is OAuth2. Otherwise this should be empty.
     * @return SecurityRequirement
     */
    public function createSecurityRequirement(array $scopes) : SecurityRequirement
    {
        return new SecurityRequirement($this->m_name, $scopes);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getSecurityScheme() : InterfaceSecurityScheme { return $this->m_scheme; }
}
