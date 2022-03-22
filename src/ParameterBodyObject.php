<?php

/*
 *
 */

namespace Programster\Swagger;

class ParameterBodyObject extends Parameter
{
    protected readonly Definition $m_definition;


    public function __construct(
        string $name,
        string $description,
        bool $required,
        Definition $objectDefinition,
        bool $deprecated = false
    )
    {
        parent::__construct($name, $description, $required, Type::OBJECT, ParameterLocation::BODY, $deprecated);
        $this->m_definition = $objectDefinition;
    }


    public function toArray()
    {
        return array(
            'in' => $this->m_in,
            'name' => $this->m_name,
            'description' => $this->m_description,
            'schema' => array(
                '$ref' => "#/definitions/{$this->m_definition->getName()}",
            ),
            'deprecated' => $this->m_deprecated,
        );
    }
}

