<?php

namespace Programster\Swagger\OAuth;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * A scope that an OAuth flow offers, e.g. "read:users".
 */
final readonly class Scope
{
    private string $m_name;
    private string $m_description;


    /**
     * @param string $name - the name of the scope, e.g. "read:users".
     * @param string $description - a short description of what the scope gives access to.
     * @throws InvalidValueException - if the name is empty.
     */
    public function __construct(string $name, string $description)
    {
        Validator::assertNotEmpty($name, "The scope's name");
        $this->m_name = $name;
        $this->m_description = $description;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getDescription() : string { return $this->m_description; }
}
