<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/object.html
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaObject implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly ?SchemaMap $m_properties;
    private readonly ?array $m_requiredProperties;
    private readonly ?int $m_minProperties;
    private readonly ?int $m_maxProperties;


    /**
     * Create a Schema for an object.
     * @param string|null $description - A description of the object.
     * @param SchemaMap|null $properties - the properties that may form the object.
     * @param string[]|null $requiredProperties - a list of property names that MUST be provided
     * @param int|null $minProperties - the minimum number of properties to expect in the object.
     * @param int|null $maxProperties - the maximum number of properties to expect in the object.
     */
    public function __construct(
        ?string $description = null,
        ?SchemaMap $properties = null,
        ?array $requiredProperties = null,
        ?int $minProperties = null,
        ?int $maxProperties = null
    )
    {
        $this->m_description = $description;
        $this->m_properties = $properties;
        $this->m_requiredProperties = $requiredProperties;
        $this->m_minProperties = $minProperties;
        $this->m_maxProperties = $maxProperties;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'properties' => $this->m_properties,
            'required' => $this->m_requiredProperties,
            'minProperties' => $this->m_minProperties,
            'maxProperties' => $this->m_maxProperties,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['type' => Type::OBJECT], array_filter($optionalFields, $isProvided));
    }
}
