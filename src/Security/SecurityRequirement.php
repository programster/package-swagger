<?php

namespace Programster\Swagger\Security;

use Programster\Swagger\Exceptions\DuplicateKeyException;


/**
 * A set of security schemes which are all needed at once, e.g. an API key and OAuth2. Create one with
 * NamedSecurityScheme::createSecurityRequirement() when only one scheme is needed. To allow any one of several
 * requirements instead, put each in a SecurityRequirementCollection.
 * https://swagger.io/specification/v3/#security-requirement-object
 */
final readonly class SecurityRequirement implements \JsonSerializable
{
    private RequiredSchemeCollection $m_schemes;


    /**
     * @param RequiredScheme ...$schemes - the schemes that are all needed. With none, the requirement is met without
     * any security, which makes security optional when it's one of several requirements.
     *
     * @throws DuplicateKeyException - if the same scheme is given twice.
     */
    public function __construct(RequiredScheme ...$schemes)
    {
        $this->m_schemes = new RequiredSchemeCollection(...$schemes);
    }


    /**
     * Get the names of the security schemes that this requirement needs.
     * @return string[]
     */
    public function getSchemeNames() : array
    {
        return $this->m_schemes->getKeys();
    }


    public function jsonSerialize() : \stdClass
    {
        return $this->m_schemes->jsonSerialize();
    }
}
