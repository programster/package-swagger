<?php

/*
 *
 */

namespace Programster\Swagger;

class ParameterBodyObject extends Parameter
{
    protected $m_schema;
    protected Definition $m_definition;


    public function __construct(string $name, string $description, bool $required, Definition $objectDefinition)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_required = $required;
        $this->m_type = "object";
        $this->m_in = ParameterLocation::createBody();
        $this->m_definition = $objectDefinition;
    }


    public function toArray()
    {
        return array(
            'in' => (string) $this->m_in,
            'name' => $this->m_name,
            'description' => $this->m_description,
            'schema' => array(
                '$ref' => "#/definitions/{$this->m_definition->getName()}",
            )
        );
    }
}

