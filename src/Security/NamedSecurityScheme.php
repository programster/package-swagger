<?php

namespace Programster\Swagger\Security;

class NamedSecurityScheme
{
    public function __construct(
        private readonly string $name,
        private readonly InterfaceSecurityScheme $scheme
    )
    {

    }

    public function createSecurityRequirement(array $scopes) : SecurityRequirement
    {
        return new SecurityRequirement($this->name, $scopes);
    }

    public function getName() : string { return $this->name; }
    public function getSecurityScheme() : InterfaceSecurityScheme { return $this->scheme; }
}
