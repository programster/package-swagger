<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * A security scheme with a name, to put in the components (in a SecuritySchemeMap), so that security requirements
 * can use it.
 */
final readonly class NamedSecurityScheme
{
    private string $m_name;
    private SecuritySchemeInterface $m_scheme;


    /**
     * @param string $name - the name of the scheme, e.g. "apiKey". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param SecuritySchemeInterface $scheme - the scheme.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, SecuritySchemeInterface $scheme)
    {
        Validator::assertComponentName($name, "The security scheme's name");
        $this->m_name = $name;
        $this->m_scheme = $scheme;
    }


    /**
     * Create a requirement for just this scheme, for the whole document or an operation. To require more than one
     * scheme at once, create a SecurityRequirement with a RequiredScheme for each.
     *
     * @param string ...$scopes - the scopes that are needed. Only OAuth2 and OpenIdConnect schemes have scopes. An
     * OAuth2 scheme's flows must offer them.
     *
     * @return SecurityRequirement
     *
     * @throws InvalidValueException - if a scope is given for a scheme that doesn't have any, or isn't offered.
     */
    public function createSecurityRequirement(string ...$scopes) : SecurityRequirement
    {
        return new SecurityRequirement(new RequiredScheme($this, ...$scopes));
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getSecurityScheme() : SecuritySchemeInterface { return $this->m_scheme; }
}
