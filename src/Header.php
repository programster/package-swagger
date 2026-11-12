<?php

/*
 * https://swagger.io/specification/#header-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Schemas\SchemaInterface;


class Header implements \JsonSerializable
{
    private readonly string $m_name;
    private readonly ?ContentMap $m_content;
    private readonly ?bool $m_required;
    private readonly SchemaInterface|Reference|null $m_schema;
    private readonly ?string $m_description;
    private readonly ?bool $m_deprecated;
    private readonly ?bool $m_allowEmptyValue;
    private readonly ?bool $m_explode;
    private readonly ?bool $m_allowReserved;
    private readonly ExternalValueExample|ValueExample|null $m_example;
    private readonly ?ExampleMap $m_examples;


    /**
     * Create a header. Use createFromSchema() or createFromContentMap() to create one.
     */
    private function __construct(
        string $name,
        ?ContentMap $content,
        ?bool $required,
        SchemaInterface|Reference|null $schema,
        ?string $description,
        ?bool $deprecated,
        ?bool $allowEmptyValue,
        ?bool $explode,
        ?bool $allowReserved,
        ExternalValueExample|ValueExample|null $example,
        ?ExampleMap $examples
    )
    {
        $this->m_name = $name;
        $this->m_content = $content;
        $this->m_required = $required;
        $this->m_schema = $schema;
        $this->m_description = $description;
        $this->m_deprecated = $deprecated;
        $this->m_allowEmptyValue = $allowEmptyValue;
        $this->m_explode = $explode;
        $this->m_allowReserved = $allowReserved;
        $this->m_example = $example;
        $this->m_examples = $examples;
    }


    /**
     * Creates a header object with the help of a provided Schema (or reference to a schema).
     * @param string $name - the name of the header, e.g. "Authorization".
     * @param SchemaInterface|Reference $schema - the schema for the header's value, or a reference to one.
     * @param bool|null $required - whether the header is required.
     * @param string|null $description - a description of the header.
     * @param bool|null $deprecated - whether the header is deprecated.
     * @param bool|null $allowEmptyValue - whether the header may be sent with an empty value.
     * @param bool|null $explode - whether array or object values generate separate values.
     * @param bool|null $allowReserved - whether the value may contain reserved characters.
     * @param ExampleMap|ExternalValueExample|ValueExample|null $example - an example, or a map of named examples.
     * @return Header
     */
    public static function createFromSchema(
        string $name,
        SchemaInterface|Reference $schema,
        ?bool $required = null,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        ExampleMap|ExternalValueExample|ValueExample|null $example = null
    ) : Header
    {
        $isExampleMap = $example instanceof ExampleMap;

        return new Header(
            name: $name,
            content: null,
            required: $required,
            schema: $schema,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            explode: $explode,
            allowReserved: $allowReserved,
            example: $isExampleMap ? null : $example,
            examples: $isExampleMap ? $example : null
        );
    }


    /**
     * Creates a header object with the help of a provided ContentMap object.
     * @param string $name - the name of the header, e.g. "Authorization".
     * @param ContentMap $content - the content of the header's value.
     * @param bool|null $required - whether the header is required.
     * @param string|null $description - a description of the header.
     * @param bool|null $deprecated - whether the header is deprecated.
     * @param bool|null $allowEmptyValue - whether the header may be sent with an empty value.
     * @return Header
     */
    public static function createFromContentMap(
        string $name,
        ContentMap $content,
        ?bool $required = null,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null
    ) : Header
    {
        return new Header(
            name: $name,
            content: $content,
            required: $required,
            schema: null,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            explode: null,
            allowReserved: null,
            example: null,
            examples: null,
        );
    }


    public function toArray()
    {
        $optionalFields = [
            'required' => $this->m_required,
            'description' => $this->m_description,
            'deprecated' => $this->m_deprecated,
            'allowEmptyValue' => $this->m_allowEmptyValue,
            'schema' => $this->m_schema,
            'content' => $this->m_content,
            'explode' => $this->m_explode,
            'allowReserved' => $this->m_allowReserved,
            'example' => $this->m_example,
            'examples' => $this->m_examples,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_filter($optionalFields, $isProvided);
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
}
