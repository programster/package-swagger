<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/numeric.html#integer
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaInt implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly ?int $m_minimum;
    private readonly ?int $m_maximum;
    private readonly ?int $m_multipleOf;
    private readonly ?int $m_exclusiveMaximum;
    private readonly ?int $m_exclusiveMinimum;
    private readonly ?int $m_default;
    private readonly bool $m_nullable;


    public function __construct(
        ?string $description = null,
        ?int $minimum = null,
        ?int $maximum = null,
        ?int $multipleOf = null,
        ?int $exclusiveMaximum = null,
        ?int $exclusiveMinimum = null,
        ?int $default = null,
        bool $nullable = false,
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
        return array_merge(['type' => Type::INT], array_filter($optionalFields, $isProvided));
    }
}
