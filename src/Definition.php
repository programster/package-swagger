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
    
    public function __construct($name, $description)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_properties = array();
    }
    
    
    public function addProperty($name, $type, $description="")
    {
        $propertyArrayForm = array("type" => $type);
        
        if ($description !== "")
        {
            $propertyArrayForm["description"] = $description;
        }
        
        $this->m_properties[$name] = $propertyArrayForm;
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