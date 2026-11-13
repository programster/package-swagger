<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema for an integer.
 * https://swagger.io/specification/v3/#data-types
 */
final readonly class SchemaInt implements SchemaInterface
{
    private ?string $m_description;
    private int|null $m_minimum;
    private int|null $m_maximum;
    private bool $m_exclusiveMinimum;
    private bool $m_exclusiveMaximum;
    private int|null $m_multipleOf;
    private int|null $m_default;
    private ?IntegerFormat $m_format;
    private bool $m_nullable;
    private ?array $m_enum;
    private int|null $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ?Xml $m_xml;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param int|null $minimum - the lowest the value can be.
     *
     * @param int|null $maximum - the highest the value can be.
     *
     * @param bool $exclusiveMinimum - true if the value must be greater than the minimum, rather than being allowed
     * to equal it. This needs a minimum.
     *
     * @param bool $exclusiveMaximum - true if the value must be less than the maximum, rather than being allowed to
     * equal it. This needs a maximum.
     *
     * @param int|null $multipleOf - a number that the value must be a multiple of. It must be greater than 0.
     *
     * @param int|null $default - the value to assume when none is given.
     *
     * @param IntegerFormat|null $format - the format of the value, e.g. IntegerFormat::INT64.
     *
     * @param bool $nullable - whether the value may be null as well.
     *
     * @param int[]|null $enum - the only values that the value can have, e.g. [1, 2, 3]. If the schema is
     * nullable, null is added to them in the output, as OpenAPI requires.
     *
     * @param int|null $example - an example of the value.
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
     * @throws InvalidValueException - if the minimum is greater than the maximum, an exclusive flag is set without
     * its bound, multipleOf isn't greater than 0, the enum is empty or doesn't include the default and example, or
     * the XML is wrapped.
     */
    public function __construct(
        ?string $description = null,
        int|null $minimum = null,
        int|null $maximum = null,
        bool $exclusiveMinimum = false,
        bool $exclusiveMaximum = false,
        int|null $multipleOf = null,
        int|null $default = null,
        ?IntegerFormat $format = null,
        bool $nullable = false,
        ?array $enum = null,
        int|null $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ?Xml $xml = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotGreater($minimum, $maximum, "minimum", "maximum");

        if ($exclusiveMinimum && $minimum === null)
        {
            throw new InvalidValueException("exclusiveMinimum needs a minimum, as it makes the minimum exclusive.");
        }

        if ($exclusiveMaximum && $maximum === null)
        {
            throw new InvalidValueException("exclusiveMaximum needs a maximum, as it makes the maximum exclusive.");
        }

        if ($multipleOf !== null && $multipleOf <= 0)
        {
            throw new InvalidValueException("multipleOf must be greater than 0, but was {$multipleOf}.");
        }

        $isOfType = function (mixed $value) : bool { return is_int($value); };
        Validator::assertEnum($enum, $isOfType, "integer", $default, $example);
        Validator::assertXmlFits($xml, Type::INT);
        $this->m_description = $description;
        $this->m_minimum = $minimum;
        $this->m_maximum = $maximum;
        $this->m_exclusiveMinimum = $exclusiveMinimum;
        $this->m_exclusiveMaximum = $exclusiveMaximum;
        $this->m_multipleOf = $multipleOf;
        $this->m_default = $default;
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
            'type' => Type::INT->value,
            'title' => $this->m_title,
            'description' => $this->m_description,
            'format' => $this->m_format?->value,
            'minimum' => $this->m_minimum,
            'exclusiveMinimum' => $this->m_exclusiveMinimum ? true : null,
            'maximum' => $this->m_maximum,
            'exclusiveMaximum' => $this->m_exclusiveMaximum ? true : null,
            'multipleOf' => $this->m_multipleOf,
            'default' => $this->m_default,
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
