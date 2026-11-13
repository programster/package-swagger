<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\ParameterReference;


/**
 * A parameter with a name, to put in the components (in a ParameterMap).
 */
final readonly class NamedParameter
{
    private string $m_name;
    private ParameterInterface $m_parameter;


    /**
     * @param string $name - the name of the parameter, e.g. "userId". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param ParameterInterface $parameter - the parameter, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, ParameterInterface $parameter)
    {
        Validator::assertComponentName($name, "The parameter's name");
        $this->m_name = $name;
        $this->m_parameter = $parameter;
    }


    /**
     * Create a reference to this parameter, for using it elsewhere, once it is in the components.
     * @return ParameterReference
     */
    public function createReference() : ParameterReference
    {
        return ParameterReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getParameter() : ParameterInterface { return $this->m_parameter; }
}
