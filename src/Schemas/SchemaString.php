<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema for a string.
 * https://swagger.io/specification/v3/#data-types
 */
final readonly class SchemaString implements SchemaInterface
{
    private ?string $m_description;
    private ?string $m_default;
    private ?int $m_minLength;
    private ?int $m_maxLength;
    private ?string $m_pattern;
    private ?StringFormat $m_format;
    private bool $m_nullable;
    private ?array $m_enum;
    private ?string $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ?Xml $m_xml;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the string. CommonMark syntax may be used.
     *
     * @param string|null $default - the value to assume when none is given.
     *
     * @param int|null $minLength - the minimum number of characters.
     *
     * @param int|null $maxLength - the maximum number of characters.
     *
     * @param string|null $pattern - a regular expression (in the ECMA 262 dialect) that the string must match.
     *
     * @param StringFormat|null $format - the format of the string, e.g. StringFormat::EMAIL.
     *
     * @param bool $nullable - whether the value may be null as well as a string.
     *
     * @param string[]|null $enum - the only values that the string can have, e.g. ["active", "suspended"]. For a
     * string-backed PHP enum, this can be array_column(MyEnum::cases(), 'value'). If the schema is nullable, null is
     * added to them in the output, as OpenAPI requires.
     *
     * @param string|null $example - an example of the string.
     *
     * @param string|null $title - a short title for the schema.
     *
     * @param bool $deprecated - true if the value is deprecated, and shouldn't be used.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the value.
     *
     * @param Xml|null $xml - how the value is represented in XML.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the schema.
     *
     * @throws InvalidValueException - if minLength or maxLength is negative, minLength is greater than maxLength, the
     * enum is empty or doesn't include the default and example, or the XML is wrapped.
     */
    public function __construct(
        ?string $description = null,
        ?string $default = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?StringFormat $format = null,
        bool $nullable = false,
        ?array $enum = null,
        ?string $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ?Xml $xml = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotNegative($minLength, "minLength");
        Validator::assertNotNegative($maxLength, "maxLength");
        Validator::assertNotGreater($minLength, $maxLength, "minLength", "maxLength");
        $isString = function (mixed $value) : bool { return is_string($value); };
        Validator::assertEnum($enum, $isString, "string", $default, $example);
        Validator::assertXmlFits($xml, Type::STRING);
        $this->m_description = $description;
        $this->m_default = $default;
        $this->m_minLength = $minLength;
        $this->m_maxLength = $maxLength;
        $this->m_pattern = $pattern;
        $this->m_format = $format;
        $this->m_nullable = $nullable;
        $this->m_enum = ($enum === null) ? null : array_values($enum);
        $this->m_example = $example;
        $this->m_title = $title;
        $this->m_deprecated = $deprecated;
        $this->m_externalDocs = $externalDocs;
        $this->m_xml = $xml;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $enum = $this->m_enum;

        if ($enum !== null && $this->m_nullable)
        {
            $enum[] = null;
        }

        $fields = [
            'type' => Type::STRING->value,
            'title' => $this->m_title,
            'description' => $this->m_description,
            'default' => $this->m_default,
            'minLength' => $this->m_minLength,
            'maxLength' => $this->m_maxLength,
            'pattern' => $this->m_pattern,
            'format' => $this->m_format?->value,
            'enum' => $enum,
            'nullable' => $this->m_nullable ? true : null,
            'example' => $this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
            'xml' => $this->m_xml,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
