<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/boolean.html
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaBool implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly ?bool $m_default;


    public function __construct(?string $description = null, ?bool $default = null)
    {
        $this->m_description = $description;
        $this->m_default = $default;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'default' => $this->m_default,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['type' => Type::BOOL], array_filter($optionalFields, $isProvided));
    }
}
