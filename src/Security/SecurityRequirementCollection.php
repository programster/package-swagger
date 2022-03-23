<?php

/*
 * A collection that can only take Parameters
 */

namespace Programster\Swagger\Security;

use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\ImplicitOauth2;
use Programster\Swagger\Security\InterfaceSecurityScheme;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NoSecurity;


final class SecurityRequirementCollection implements \JsonSerializable
{
    private readonly array $schemes;

    public function __construct(SecurityRequirement ...$requirements)
    {
        $this->schemes = $requirements;
    }


    public function jsonSerialize(): mixed
    {
        return $this->schemes;
    }
}
