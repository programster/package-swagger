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
    private array $m_requiredPropertyNames;


    public function __construct($name, $description, DefinitionPropertyInterface ...$properties)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_properties = array();
        $this->m_requiredPropertyNames = array();

        foreach ($properties as $property)
        {
            $propertyArrayForm = $property->jsonSerialize();
            $this->m_properties[$property->getName()] = $propertyArrayForm;

            /* @var $property DefinitionPropertyInterface */
            if ($property->isRequired())
            {
                $this->m_requiredPropertyNames[] = $property->getName();
            }
        }
    }


    public function jsonSerialize()
    {
        $arrayForm = array(
            "description" => $this->m_description,
            "properties" => $this->m_properties
        );

        if (count($this->m_requiredPropertyNames) > 0)
        {
            $arrayForm["required"] = $this->m_requiredPropertyNames;
        }
        
        return $arrayForm;
    }


    public function getName() { return $this->m_name; }
}