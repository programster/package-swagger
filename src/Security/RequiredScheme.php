<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\InvalidValueException;


/**
 * A security scheme that a security requirement needs, with the scopes it needs, if the scheme is OAuth2 or
 * OpenIdConnect.
 */
final readonly class RequiredScheme
{
    private NamedSecurityScheme $m_scheme;
    private array $m_scopes;


    /**
     * @param NamedSecurityScheme $scheme - the scheme that is needed.
     *
     * @param string ...$scopes - the scopes that are needed. Only OAuth2 and OpenIdConnect schemes have scopes, so
     * for any other scheme there can't be any. An OAuth2 scheme's flows must offer them.
     *
     * @throws InvalidValueException - if a scope is given for a scheme that doesn't have any, or isn't offered.
     */
    public function __construct(NamedSecurityScheme $scheme, string ...$scopes)
    {
        $securityScheme = $scheme->getSecurityScheme();

        foreach ($scopes as $scope)
        {
            if (!($securityScheme instanceof OAuth2) && !($securityScheme instanceof OpenIdConnect))
            {
                $message = "The \"{$scheme->getName()}\" security scheme doesn't have scopes, as only OAuth2 and "
                    . "OpenIdConnect schemes do, so it can't be required with the \"{$scope}\" scope.";

                throw new InvalidValueException($message);
            }

            // OpenID Connect's scopes are in its discovery document, so only OAuth2's can be checked.
            if ($securityScheme instanceof OAuth2 && $securityScheme->hasScope($scope) === false)
            {
                $message = "The \"{$scheme->getName()}\" security scheme's flows don't offer the \"{$scope}\" "
                    . "scope. Add it to the flows' ScopeMap, or check the name for typos.";

                throw new InvalidValueException($message);
            }
        }

        $this->m_scheme = $scheme;
        $this->m_scopes = array_values(array_unique($scopes));
    }


    # Accessors
    public function getSchemeName() : string { return $this->m_scheme->getName(); }
    public function getScopes() : array { return $this->m_scopes; }
}
