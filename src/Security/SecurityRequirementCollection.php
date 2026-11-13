<?php

namespace Programster\Swagger\Security;

use Ramsey\Collection\AbstractCollection;


/**
 * The security requirements for the whole document or an operation. Any one of them is enough. An empty collection
 * means no security is needed, which makes an operation public when the document requires security.
 * https://swagger.io/specification/v3/#security-requirement-object
 */
final class SecurityRequirementCollection extends AbstractCollection implements \JsonSerializable
{
    /**
     * @param SecurityRequirement ...$requirements - the requirements, any one of which is enough.
     */
    public function __construct(SecurityRequirement ...$requirements)
    {
        parent::__construct($requirements);
    }


    public function getType() : string
    {
        return SecurityRequirement::class;
    }


    /**
     * Get the names of all the security schemes that the requirements use.
     * @return string[]
     */
    public function getSchemeNames() : array
    {
        $names = [];

        foreach ($this->data as $requirement)
        {
            /** @var SecurityRequirement $requirement */
            $names = array_merge($names, $requirement->getSchemeNames());
        }

        return array_values(array_unique($names));
    }


    public function jsonSerialize() : array
    {
        return array_values($this->data);
    }
}
