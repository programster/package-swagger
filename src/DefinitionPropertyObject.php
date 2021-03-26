<?php

/*
 * A property for an object/definition.
 */

namespace Programster\Swagger;

class DefinitionPropertyObject implements DefinitionPropertyInterface
{
    private $m_object;
    private bool $m_required;
    

    public function __construct(string $name, Definition $object, bool $required)
    {
        $this->m_name = $name;
        $this->m_object = $object;
        $this->m_required = $required;
    }


    public function jsonSerialize()
    {
        return array('$ref' => "#/definitions/" . $this->m_object->getName());
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function isRequired(): bool { return $this->m_required; }
}

