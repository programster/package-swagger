<?php

/*
 *
 */

namespace Programster\Swagger;

class Definition implements \JsonSerializable
{
    private readonly string $m_name;
    private readonly string $m_description;
    private readonly array $m_properties;
    private readonly array $m_requiredPropertyNames;


    public function __construct($name, $description, DefinitionPropertyInterface ...$properties)
    {
        $this->m_name = $name;
        $this->m_description = $description;

        $requiredPropertyNames = [];
        $mappedProperties = [];

        foreach ($properties as $property)
        {
            $propertyArrayForm = $property->jsonSerialize();
            $mappedProperties[$property->getName()] = $propertyArrayForm;

            /* @var $property DefinitionPropertyInterface */
            if ($property->isRequired())
            {
                $requiredPropertyNames[] = $property->getName();
            }
        }

        $this->m_properties = $mappedProperties;
        $this->m_requiredPropertyNames = $requiredPropertyNames;
    }


    public function jsonSerialize() : mixed
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


    # Accessors
    public function getName() { return $this->m_name; }
}
