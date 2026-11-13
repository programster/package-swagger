<?php

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\Schemas\SchemaInterface;


/**
 * A parameter of an operation, in its path, query string, headers or cookies. Create one with createFromSchema() for
 * most parameters, or createFromContentMap() for one whose value is in a format such as JSON.
 * https://swagger.io/specification/v3/#parameter-object
 */
final readonly class Parameter implements ParameterInterface
{
    private string $m_name;
    private ParameterLocation $m_in;
    private bool $m_required;
    private ?SchemaInterface $m_schema;
    private ?ContentMap $m_content;
    private ?string $m_description;
    private bool $m_deprecated;
    private bool $m_allowEmptyValue;
    private ?Style $m_style;
    private ?bool $m_explode;
    private bool $m_allowReserved;
    private ValueExample|ExampleMap|null $m_example;
    private ExtensionMap $m_extensions;


    /**
     * @throws InvalidValueException - if any of the values can't be used for a parameter in that location.
     */
    private function __construct(
        string $name,
        ParameterLocation $in,
        ?bool $required,
        ?SchemaInterface $schema,
        ?ContentMap $content,
        ?string $description,
        bool $deprecated,
        bool $allowEmptyValue,
        ?Style $style,
        ?bool $explode,
        bool $allowReserved,
        ValueExample|ExampleMap|null $example,
        ExtensionMap $extensions
    )
    {
        Validator::assertNotEmpty($name, "The parameter's name");
        $isPath = ($in === ParameterLocation::PATH);
        $isQuery = ($in === ParameterLocation::QUERY);

        if ($isPath && $required === false)
        {
            $message = "The \"{$name}\" parameter is in the path, so it must be required. Leave \$required out, as "
                . "it defaults to true for path parameters.";

            throw new InvalidValueException($message);
        }

        $ignoredHeaders = ["accept", "content-type", "authorization"];

        if ($in === ParameterLocation::HEADER && in_array(strtolower($name), $ignoredHeaders, true))
        {
            $message = "A \"{$name}\" header parameter would be ignored, as OpenAPI describes it elsewhere: Accept and "
                . "Content-Type by the content, and Authorization by the security schemes.";

            throw new InvalidValueException($message);
        }

        if (!$isQuery && $allowEmptyValue)
        {
            throw new InvalidValueException("allowEmptyValue can only be used for parameters in the query.");
        }

        if (!$isQuery && $allowReserved)
        {
            throw new InvalidValueException("allowReserved can only be used for parameters in the query.");
        }

        if ($style !== null)
        {
            self::assertStyleAllowedIn($style, $in);
        }

        if ($content !== null && count($content) !== 1)
        {
            throw new InvalidValueException("The \"{$name}\" parameter's content must have exactly one media type.");
        }

        $this->m_name = $name;
        $this->m_in = $in;
        $this->m_required = $isPath || $required === true;
        $this->m_schema = $schema;
        $this->m_content = ($content === null) ? null : clone $content;
        $this->m_description = $description;
        $this->m_deprecated = $deprecated;
        $this->m_allowEmptyValue = $allowEmptyValue;
        $this->m_style = $style;
        $this->m_explode = $explode;
        $this->m_allowReserved = $allowReserved;
        $this->m_example = ($example instanceof ExampleMap) ? clone $example : $example;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Create a parameter whose value is described by a schema.
     *
     * @param string $name - the name of the parameter, as it is sent in the request.
     *
     * @param ParameterLocation $in - where the parameter is sent, e.g. ParameterLocation::QUERY.
     *
     * @param SchemaInterface $schema - the schema of the parameter's value, or a reference to one.
     *
     * @param bool|null $required - whether the parameter must be sent. Defaults to true for path parameters (which
     * must be required), and false for any other.
     *
     * @param string|null $description - a description of the parameter. CommonMark syntax may be used.
     *
     * @param bool $deprecated - true if the parameter is deprecated, and shouldn't be used.
     *
     * @param bool $allowEmptyValue - true if the parameter may be sent with an empty value. Only for the query.
     *
     * @param Style|null $style - how the value is serialized, which must be one that can be used in that location.
     *
     * @param bool|null $explode - for an array or object value, whether to send it as separate parameters.
     *
     * @param bool $allowReserved - true if reserved characters (e.g. "/" and "?") are sent without being
     * percent-encoded. Only for the query.
     *
     * @param ValueExample|ExampleMap|null $example - an example of the value, or a map of named examples.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the parameter.
     *
     * @return Parameter
     *
     * @throws InvalidValueException - if any of the values can't be used for a parameter in that location.
     */
    public static function createFromSchema(
        string $name,
        ParameterLocation $in,
        SchemaInterface $schema,
        ?bool $required = null,
        ?string $description = null,
        bool $deprecated = false,
        bool $allowEmptyValue = false,
        ?Style $style = null,
        ?bool $explode = null,
        bool $allowReserved = false,
        ValueExample|ExampleMap|null $example = null,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Parameter
    {
        return new Parameter(
            name: $name,
            in: $in,
            required: $required,
            schema: $schema,
            content: null,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            style: $style,
            explode: $explode,
            allowReserved: $allowReserved,
            example: $example,
            extensions: $extensions
        );
    }


    /**
     * Create a parameter whose value is in a format such as JSON, described by a media type.
     *
     * @param string $name - the name of the parameter, as it is sent in the request.
     *
     * @param ParameterLocation $in - where the parameter is sent, e.g. ParameterLocation::QUERY.
     *
     * @param ContentMap $content - the media type of the parameter's value. It must have exactly one.
     *
     * @param bool|null $required - whether the parameter must be sent. Defaults to true for path parameters (which
     * must be required), and false for any other.
     *
     * @param string|null $description - a description of the parameter. CommonMark syntax may be used.
     *
     * @param bool $deprecated - true if the parameter is deprecated, and shouldn't be used.
     *
     * @param bool $allowEmptyValue - true if the parameter may be sent with an empty value. Only for the query.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the parameter.
     *
     * @return Parameter
     *
     * @throws InvalidValueException - if any of the values can't be used for a parameter in that location, or the
     * content has more than one media type.
     */
    public static function createFromContentMap(
        string $name,
        ParameterLocation $in,
        ContentMap $content,
        ?bool $required = null,
        ?string $description = null,
        bool $deprecated = false,
        bool $allowEmptyValue = false,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Parameter
    {
        return new Parameter(
            name: $name,
            in: $in,
            required: $required,
            schema: null,
            content: $content,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            style: null,
            explode: null,
            allowReserved: false,
            example: null,
            extensions: $extensions
        );
    }


    public function jsonSerialize() : \stdClass
    {
        $isExampleMap = $this->m_example instanceof ExampleMap;

        $fields = [
            'name' => $this->m_name,
            'in' => $this->m_in->value,
            'description' => $this->m_description,
            'required' => $this->m_required ? true : null,
            'deprecated' => $this->m_deprecated ? true : null,
            'allowEmptyValue' => $this->m_allowEmptyValue ? true : null,
            'style' => $this->m_style?->value,
            'explode' => $this->m_explode,
            'allowReserved' => $this->m_allowReserved ? true : null,
            'schema' => $this->m_schema,
            'content' => $this->m_content,
            'example' => $isExampleMap ? null : $this->m_example,
            'examples' => $isExampleMap ? $this->m_example : null,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    /**
     * @throws InvalidValueException - if the style can't be used for parameters in that location.
     */
    private static function assertStyleAllowedIn(Style $style, ParameterLocation $in) : void
    {
        $allowedLocations = match ($style) {
            Style::MATRIX, Style::LABEL => [ParameterLocation::PATH],
            Style::FORM => [ParameterLocation::QUERY, ParameterLocation::COOKIE],
            Style::SIMPLE => [ParameterLocation::PATH, ParameterLocation::HEADER],
            Style::SPACE_DELIMITED, Style::PIPE_DELIMITED, Style::DEEP_OBJECT => [ParameterLocation::QUERY],
        };

        if (!in_array($in, $allowedLocations, true))
        {
            $locations = implode("' or '", array_column($allowedLocations, 'value'));
            $message = "The {$style->value} style can only be used for parameters in the '{$locations}'.";
            throw new InvalidValueException($message);
        }
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getLocation() : ParameterLocation { return $this->m_in; }
}
