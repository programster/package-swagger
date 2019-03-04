<?php

/* 
 * 
 */

namespace Programster\Swagger;

class Definition implements \JsonSerializable
{
    private $m_name;
    private $m_description;
    private $m_properties;
    
    
    public function __construct($name, $description, DefinitionProperty ...$properties)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_properties = array();
        
        
        foreach ($properties as $property)
        {
            $propertyArrayForm = array("type" => $property->getType());
        
            if ($property->getDescription() !== "")
            {
                $propertyArrayForm["description"] = $property->getDescription();
            }
            
            $this->m_properties[$property->getName()] = $propertyArrayForm;
        }
        
    }
    
    
    public function jsonSerialize()
    {
        return array(
            "description" => $this->m_description,
            "properties" => $this->m_properties
        );
    }
    
    
    public function get_name() { return $this->m_name; }

}