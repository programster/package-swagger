<?php

namespace Programster\Swagger\Security;

use JetBrains\PhpStorm\Internal\TentativeType;

class SecurityRequirement implements \JsonSerializable
{
    private array $arrayForm;


    /**
     * Create a security requirement object
     * https://swagger.io/specification/#security-requirement-object
     * @param string $schemeName - the unique name (ID) of the security scheme within the components object
     * @param array $scopes - any scopes that need to be utilized to run the operation.
     */
    public function __construct(string $schemeName, array $scopes)
    {
        $this->arrayForm = [
            $schemeName => $scopes
        ];
    }


    /**
     * Add another scheme to this requirement.
     * WARNING: This will require all of these schemes to be satisfied. If you want to specify that *either* scheme
     * can be satisfied to pass security, then you need to create separate SecurityRequirement objects and pass them to
     * the SecurityRequriementCollection.
     * @param string $schemeName
     * @param array $scopes
     * @return void
     * @throws \Exception
     */
    public function add(string $schemeName, array $scopes)
    {
        if (isset($this->arrayForm[$schemeName]))
        {
            throw new \Exception("Duplicate scheme name in SecurityRequirement: {$schemeName}");
        }

        $this->arrayForm[$this->schemeName] = $this->scopes;
    }


    public function jsonSerialize(): mixed
    {
        return $this->arrayForm;
    }


    public function getSchemeName() : string { return $this->schemeName; }
    public function getScopes() : array { return $this->scopes; }


}
