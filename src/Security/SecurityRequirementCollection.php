<?php

/*
 * A collection that can only take SecurityRequirements
 */

namespace Programster\Swagger\Security;


final class SecurityRequirementCollection implements \JsonSerializable
{
    private readonly array $m_requirements;


    public function __construct(SecurityRequirement ...$requirements)
    {
        $this->m_requirements = $requirements;
    }


    public function jsonSerialize() : array
    {
        return $this->m_requirements;
    }
}
