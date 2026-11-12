<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/string.html
 */

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Format;
use Programster\Swagger\Type;


class SchemaString implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly ?string $m_default;
    private readonly ?int $m_minLength;
    private readonly ?int $m_maxLength;
    private readonly ?string $m_pattern;
    private readonly ?Format $m_format;
    private readonly bool $m_nullable;


    public function __construct(
        ?string $description = null,
        ?string $default = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?Format $format = null,
        bool $nullable = false
    )
    {
        $this->m_description = $description;
        $this->m_default = $default;
        $this->m_minLength = $minLength;
        $this->m_maxLength = $maxLength;
        $this->m_pattern = $pattern;
        $this->m_format = $format;
        $this->m_nullable = $nullable;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'default' => $this->m_default,
            'minLength' => $this->m_minLength,
            'maxLength' => $this->m_maxLength,
            'pattern' => $this->m_pattern,
            'format' => $this->m_format,
            'nullable' => $this->m_nullable ? true : null,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['type' => Type::STRING], array_filter($optionalFields, $isProvided));
    }
}
