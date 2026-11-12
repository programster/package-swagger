<?php

namespace Programster\Swagger\Security;


class SecurityRequirement implements \JsonSerializable
{
    private readonly string $m_schemeName;
    private readonly array $m_scopes;
    private array $m_arrayForm;


    /**
     * Create a security requirement object.
     * https://swagger.io/specification/#security-requirement-object
     *
     * @param string $schemeName - the unique name (ID) of the security scheme within the components object.
     *
     * @param string[] $scopes - any scopes that need to be utilized to run the operation.
     *
     * getSchemeName() and getScopes() return the scheme this is created with, not any that are added to it later with
     * add().
     */
    public function __construct(string $schemeName, array $scopes)
    {
        $this->m_schemeName = $schemeName;
        $this->m_scopes = $scopes;
        $this->m_arrayForm = [$schemeName => $scopes];
    }


    /**
     * Add another scheme to this requirement.
     * WARNING: This will require all of these schemes to be satisfied. If you want to specify that *either* scheme
     * can be satisfied to pass security, then you need to create separate SecurityRequirement objects and pass them to
     * the SecurityRequirementCollection.
     *
     * @param string $schemeName - the unique name (ID) of the security scheme within the components object.
     *
     * @param string[] $scopes - any scopes that need to be utilized to run the operation.
     *
     * @return void
     *
     * @throws \Exception - if this requirement already has a scheme with that name.
     */
    public function add(string $schemeName, array $scopes)
    {
        if (isset($this->m_arrayForm[$schemeName]))
        {
            throw new \Exception("Duplicate scheme name in SecurityRequirement: {$schemeName}");
        }

        $this->m_arrayForm[$schemeName] = $scopes;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_arrayForm;
    }


    # Accessors
    public function getSchemeName() : string { return $this->m_schemeName; }
    public function getScopes() : array { return $this->m_scopes; }
}
