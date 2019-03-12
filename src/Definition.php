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
    
    
    public function __construct($name, $description, DefinitionPropertyInterface ...$properties)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_properties = array();
        
        
        foreach ($properties as $property)
        {
            $propertyArrayForm = $property->jsonSerialize();
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
    
    
    public function getName() { return $this->m_name; }

}