<?php

/*
 * https://swagger.io/specification/#parameter-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Schemas\SchemaInterface;

class Parameter implements \JsonSerializable
{
    private readonly string $m_name;
    private readonly ParameterLocation $m_in;
    private readonly bool $m_required;
    private readonly ?ContentMap $m_content;
    private readonly SchemaInterface|Reference|null $m_schema;
    private readonly ?string $m_description;
    private readonly ?bool $m_deprecated;
    private readonly ?bool $m_allowEmptyValue;
    private readonly ?Style $m_style;
    private readonly ?bool $m_explode;
    private readonly ?bool $m_allowReserved;
    private readonly mixed $m_example;
    private readonly ?ExampleMap $m_examples;


    /**
     * Create a parameter. Use createFromSchema() or createFromContentMap() to create one.
     * https://swagger.io/specification/#parameter-object
     *
     * @throws \Exception - if allowEmptyValue is set to true for a parameter that isn't in the query, or the style
     * can't be used for parameters in the parameter's location.
     */
    private function __construct(
        string $name,
        ParameterLocation $in,
        bool $required,
        ?ContentMap $content,
        SchemaInterface|Reference|null $schema,
        ?string $description,
        ?bool $deprecated,
        ?bool $allowEmptyValue,
        ?Style $style,
        ?bool $explode,
        ?bool $allowReserved,
        mixed $example,
        ?ExampleMap $examples
    )
    {
        if ($in !== ParameterLocation::QUERY && $allowEmptyValue === true)
        {
            throw new \Exception("allowEmptyValue can only be set to true for parameters located in the query");
        }

        if ($style !== null)
        {
            self::assertStyleAllowedIn($style, $in);
        }

        $this->m_name = $name;
        $this->m_in = $in;
        $this->m_required = $required;
        $this->m_content = $content;
        $this->m_schema = $schema;
        $this->m_description = $description;
        $this->m_deprecated = $deprecated;
        $this->m_allowEmptyValue = $allowEmptyValue;
        $this->m_style = $style;
        $this->m_explode = $explode;
        $this->m_allowReserved = $allowReserved;
        $this->m_example = $example;
        $this->m_examples = $examples;
    }


    /**
     * Creates a parameter object with the help of a provided Schema (or reference to a schema).
     *
     * @param string $name - the name of the parameter, as it is sent in the request.
     *
     * @param ParameterLocation $in - where the parameter goes, e.g. ParameterLocation::QUERY.
     *
     * @param bool $required - whether the parameter is required.
     *
     * @param SchemaInterface|Reference $schema - the schema for the parameter's value, or a reference to one.
     *
     * @param string|null $description - a description of the parameter.
     *
     * @param bool|null $deprecated - whether the parameter is deprecated.
     *
     * @param bool|null $allowEmptyValue - whether the parameter may be sent with an empty value. This can only be
     * set to true for parameters in the query.
     *
     * @param Style|string|null $style - how the parameter's value is serialized. A string must be one of the values
     * of the Style enum, e.g. "form".
     *
     * @param bool|null $explode - whether array or object values generate separate parameters.
     *
     * @param bool|null $allowReserved - whether the value may contain reserved characters.
     *
     * @param mixed $example - an example value or example object, or an ExampleMap of named examples.
     *
     * @return Parameter
     *
     * @throws \Exception - if allowEmptyValue or the style can't be used for parameters in that location.
     * @throws \ValueError - if $style is a string that isn't one of the values of the Style enum.
     */
    public static function createFromSchema(
        string $name,
        ParameterLocation $in,
        bool $required,
        SchemaInterface|Reference $schema,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        Style|string|null $style = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        mixed $example = null
    )
    {
        $isExampleMap = $example instanceof ExampleMap;

        return new Parameter(
            name: $name,
            in: $in,
            required: $required,
            content: null,
            schema: $schema,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            style: is_string($style) ? Style::from($style) : $style,
            explode: $explode,
            allowReserved: $allowReserved,
            example: $isExampleMap ? null : $example,
            examples: $isExampleMap ? $example : null
        );
    }


    /**
     * Creates a parameter object with the help of a provided ContentMap object.
     *
     * @param string $name - the name of the parameter, as it is sent in the request.
     *
     * @param ParameterLocation $in - where the parameter goes, e.g. ParameterLocation::QUERY.
     *
     * @param bool $required - whether the parameter is required.
     *
     * @param ContentMap $content - the content of the parameter's value.
     *
     * @param string|null $description - a description of the parameter.
     *
     * @param bool|null $deprecated - whether the parameter is deprecated.
     *
     * @param bool|null $allowEmptyValue - whether the parameter may be sent with an empty value. This can only be
     * set to true for parameters in the query.
     *
     * @return Parameter
     *
     * @throws \Exception - if allowEmptyValue is set to true for a parameter that isn't in the query.
     */
    public static function createFromContentMap(
        string $name,
        ParameterLocation $in,
        bool $required,
        ContentMap $content,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null
    )
    {
        return new Parameter(
            name: $name,
            in: $in,
            required: $required,
            content: $content,
            schema: null,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            style: null,
            explode: null,
            allowReserved: null,
            example: null,
            examples: null
        );
    }


    public function toArray()
    {
        $requiredFields = [
            'name' => $this->m_name,
            'in' => $this->m_in,
            'required' => $this->m_required,
        ];

        $optionalFields = [
            'description' => $this->m_description,
            'deprecated' => $this->m_deprecated,
            'allowEmptyValue' => $this->m_allowEmptyValue,
            'schema' => $this->m_schema,
            'content' => $this->m_content,
            'style' => $this->m_style,
            'explode' => $this->m_explode,
            'allowReserved' => $this->m_allowReserved,
            'example' => $this->m_example,
            'examples' => $this->m_examples,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge($requiredFields, array_filter($optionalFields, $isProvided));
    }


    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }


    /**
     * @throws \Exception - if the style can't be used for parameters in that location.
     */
    private static function assertStyleAllowedIn(Style $style, ParameterLocation $in) : void
    {
        $allowedLocations = match ($style) {
            Style::MATRIX, Style::LABEL => [ParameterLocation::PATH],
            Style::FORM => [ParameterLocation::QUERY, ParameterLocation::COOKIE],
            Style::SIMPLE => [ParameterLocation::PATH, ParameterLocation::HEADER],
            Style::SPACE_DELIMITED, Style::PIPE_DELIMITED, Style::DEEP_OBJECT => [ParameterLocation::QUERY],
        };

        if (in_array($in, $allowedLocations, true) === false)
        {
            $locations = implode("' or '", array_column($allowedLocations, 'value'));
            throw new \Exception("The {$style->value} style is only applicable to parameters in the '{$locations}'");
        }
    }
}
