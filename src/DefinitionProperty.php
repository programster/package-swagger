<?php

/*
 * A property for an object/definition.
 */

namespace Programster\Swagger;

final class DefinitionProperty implements DefinitionPropertyInterface
{
    private $m_name;
    private $m_type;
    private $m_description;
    private $m_arrayItemType =  null; // only applies for arrays
    private bool $m_required;


    private function __construct()
    {
        // do nothing - public static creator methods take care of everything
    }


    /**
     * Create a basic property (e.g. an integer, string, number, etc).
     * @param string $name - the name of the property.
     * @param \Programster\Swagger\Type $type - the type of the attribute e.g. number/string/int etc
     * @param string $description - the description of the property
     * @return \Programster\Swagger\DefinitionProperty
     */
    public static function createBasic(string $name, Type $type, string $description, bool $required) : DefinitionProperty
    {
        $definitionProperty = new DefinitionProperty();
        $definitionProperty->m_name = $name;
        $definitionProperty->m_type = $type;
        $definitionProperty->m_description = $description;
        $definitionProperty->m_required = $required;
        return $definitionProperty;
    }


    /**
     * Create a property that is an array of integers/strings/etc
     * @param string $name - the name of the property.
     * @param \Programster\Swagger\Type $itemType - the type for the items in the array, e.g. number/string/int etc
     * @param string $description - the description of the property
     * @return \Programster\Swagger\DefinitionProperty - the created property
     */
    public static function createArray(string $name, Type $itemType, string $description, bool $required)
    {
        $definitionProperty = new DefinitionProperty();
        $definitionProperty->m_name = $name;
        $definitionProperty->m_type = "array";
        $definitionProperty->m_description = $description;
        $definitionProperty->m_arrayItemType = $itemType;
        $definitionProperty->m_required = $required;
        return $definitionProperty;
    }


    public function jsonSerialize()
    {
        $propertyArrayForm = array("type" => $this->m_type);

        if ($this->m_arrayItemType !== null)
        {
            $propertyArrayForm['items'] = array("type" => $this->m_arrayItemType);
        }

        if ($this->m_description !== "")
        {
            $propertyArrayForm["description"] = $this->m_description;
        }

        return $propertyArrayForm;
    }


    public function isRequired(): bool
    {
        return $this->m_required;
    }

    # Accessors
    public function getName() : string { return $this->m_name; }
}

