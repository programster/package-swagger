<?php

/* 
 * 
 */

namespace Programster\Swagger;

class Parameter implements \JsonSerializable
{
    private $m_name; //"name": "name",
    private $m_description; //"description": "The name to give the dataset.",
    private $m_required; // "required": true,
    private $m_type; //"type": "integer",
    private $m_in; //"in": "formData"
    
    
    public function __construct($name, $description, $required, Type $type, ParameterLocation $in)
    {
        if ($required !== TRUE && $required !== FALSE)
        {
            throw new \Exception("required needs to be a boolean value");
        }
        
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_required = $required;
        $this->m_type = $type;
        $this->m_in = $in;
    }
    
    
    public function jsonSerialize()
    {
        return array(
            'name' => $this->m_name,
            'description' => $this->m_description,
            'required' => $this->m_required,
            'type' => (string) $this->m_type,
            'in' => (string) $this->m_in
        );
    }
    
    # Setters
    public function setRequired($flag) { $this->m_required = $flag; }
}

