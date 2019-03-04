<?php

/* 
 * A property for an object/definition.
 */

namespace Programster\Swagger;

class DefinitionProperty
{
    private $m_name;
    private $m_type;
    private $m_description;
    
    
    public function __construct(string $name, Type $type, string $description)
    {
        $this->m_name = $name;
        $this->m_type = $type;
        $this->m_description = $description;
    }
    
    
    # Accessors
    public function getName() { return $this->m_name; }
    public function getType() { return $this->m_type; }
    public function getDescription() { return $this->m_description; }
}

