<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/object.html
 */

namespace Programster\Swagger;


class SchemaObject implements \JsonSerializable
{
    /**
     * Create a Schema for an object.
     * @param string|null $description - A description of the object.
     * @param SchemaMap|null $properties - the properties that may form the object.
     * @param array|null $requiredProperties - a list of property names that MUST be provided
     * @param int|null $minProperties - the minimum number of properties to expect in the object.
     * @param int|null $maxProperties - the maximum number of properties to expect in the object.
     */
    public function __construct(
        private readonly ?string $description = null,
        private readonly ?SchemaMap $properties = null,
        private readonly ?array $requiredProperties = null,
        private readonly ?int $minProperties = null,
        private readonly ?int $maxProperties = null
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('type' => Type::OBJECT);

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->properties !== null) { $arrayForm['properties'] = $this->properties; }
        if ($this->requiredProperties !== null) { $arrayForm['required'] = $this->requiredProperties; }
        if ($this->minProperties !== null) { $arrayForm['minProperties'] = $this->minProperties; }
        if ($this->maxProperties !== null) { $arrayForm['maxProperties'] = $this->maxProperties; }

        return $arrayForm;
    }
}
