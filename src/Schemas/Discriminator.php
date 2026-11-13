<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * Tells tools which of several schemas a value matches, from the value of one of its properties, e.g. a "petType"
 * property that is "dog" for a Dog. This can be given to a SchemaOneOf or SchemaAnyOf, or to a SchemaObject that
 * other schemas extend with SchemaAllOf.
 * https://swagger.io/specification/v3/#discriminator-object
 */
final readonly class Discriminator implements \JsonSerializable
{
    private string $m_propertyName;
    private DiscriminatorMappingMap $m_mapping;


    /**
     * @param string $propertyName - the name of the property whose value says which schema the value matches. Each
     * schema must have this property, and it must be required.
     *
     * @param DiscriminatorMappingMap $mapping - the schemas for the property's values. Without a mapping for a value,
     * the value is taken to be the name of the schema in the components, e.g. "Dog".
     *
     * @throws InvalidValueException - if the property name is empty.
     */
    public function __construct(string $propertyName, DiscriminatorMappingMap $mapping = new DiscriminatorMappingMap())
    {
        Validator::assertNotEmpty($propertyName, "The discriminator's property name");
        $this->m_propertyName = $propertyName;
        $this->m_mapping = clone $mapping;
    }


    public function jsonSerialize() : \stdClass
    {
        return JsonObject::create([
            'propertyName' => $this->m_propertyName,
            'mapping' => $this->m_mapping->isEmpty() ? null : $this->m_mapping,
        ]);
    }


    # Accessors
    public function getPropertyName() : string { return $this->m_propertyName; }
    public function getMapping() : DiscriminatorMappingMap { return clone $this->m_mapping; }
}
