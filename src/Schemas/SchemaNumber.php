<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#number
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaNumber implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly float|int|null $m_minimum;
    private readonly float|int|null $m_maximum;
    private readonly float|int|null $m_multipleOf;
    private readonly float|int|null $m_exclusiveMaximum;
    private readonly float|int|null $m_exclusiveMinimum;
    private readonly float|int|null $m_default;
    private readonly bool $m_nullable;


    public function __construct(
        ?string $description = null,
        float|int|null $minimum = null,
        float|int|null $maximum = null,
        float|int|null $multipleOf = null,
        float|int|null $exclusiveMaximum = null,
        float|int|null $exclusiveMinimum = null,
        float|int|null $default = null,
        bool $nullable = false
    )
    {
        $this->m_description = $description;
        $this->m_minimum = $minimum;
        $this->m_maximum = $maximum;
        $this->m_multipleOf = $multipleOf;
        $this->m_exclusiveMaximum = $exclusiveMaximum;
        $this->m_exclusiveMinimum = $exclusiveMinimum;
        $this->m_default = $default;
        $this->m_nullable = $nullable;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'minimum' => $this->m_minimum,
            'maximum' => $this->m_maximum,
            'multipleOf' => $this->m_multipleOf,
            'exclusiveMinimum' => $this->m_exclusiveMinimum,
            'exclusiveMaximum' => $this->m_exclusiveMaximum,
            'default' => $this->m_default,
            'nullable' => $this->m_nullable ? true : null,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['type' => Type::NUMBER], array_filter($optionalFields, $isProvided));
    }
}
