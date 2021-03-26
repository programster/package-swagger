<?php

/*
 *
 */

namespace Programster\Swagger;

class Parameter implements \JsonSerializable
{
    protected $m_name; //"name": "name",
    protected $m_description; //"description": "The name to give the dataset.",
    protected $m_required; // "required": true,
    protected $m_type; //"type": "integer",
    protected $m_in; //"in": "formData"


    public function __construct(string $name, string $description, bool $required, Type $type, ParameterLocation $in)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_required = $required;
        $this->m_type = $type;
        $this->m_in = $in;
    }


    public function toArray()
    {
        return array(
            'name' => $this->m_name,
            'description' => $this->m_description,
            'required' => $this->m_required,
            'type' => $this->m_type,
            'in' => (string) $this->m_in
        );
    }


    public function jsonSerialize()
    {
        return $this->toArray();
    }
    

    # Setters
    public function setRequired($flag) { $this->m_required = $flag; }
}

