<?php

/* 
 * A property for an object/definition.
 */

namespace Programster\Swagger;

class DefinitionProperty implements DefinitionPropertyInterface
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
    
    
    public function jsonSerialize() 
    {
        $propertyArrayForm = array("type" => (string) $this->m_type);
        
        if ($this->m_description !== "")
        {
            $propertyArrayForm["description"] = $this->m_description;
        }
        
        return $propertyArrayForm;
    }
    
    # Accessors
    public function getName() : string { return $this->m_name; }
}

