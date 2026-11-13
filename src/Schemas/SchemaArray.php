<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema for an array, every item of which matches the same schema.
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaArray implements SchemaInterface
{
    private SchemaInterface $m_items;
    private ?string $m_description;
    private ?int $m_minItems;
    private ?int $m_maxItems;
    private bool $m_uniqueItems;
    private bool $m_nullable;
    private ?array $m_default;
    private ?array $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ?Xml $m_xml;
    private ExtensionMap $m_extensions;


    /**
     * @param SchemaInterface $items - the schema that every item in the array must match. OpenAPI requires this.
     *
     * @param string|null $description - a description of the array. CommonMark syntax may be used.
     *
     * @param int|null $minItems - the minimum number of items.
     *
     * @param int|null $maxItems - the maximum number of items.
     *
     * @param bool $uniqueItems - true if no two items may be the same.
     *
     * @param bool $nullable - whether the value may be null as well as an array.
     *
     * @param array|null $default - the value to assume when none is given, which must be a list, e.g. ["a", "b"].
     *
     * @param array|null $example - an example of the array, which must be a list.
     *
     * @param string|null $title - a short title for the schema.
     *
     * @param bool $deprecated - true if the value is deprecated, and shouldn't be used.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the value.
     *
     * @param Xml|null $xml - how the array is represented in XML, e.g. whether its items are wrapped in an element.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the schema.
     *
     * @throws InvalidValueException - if minItems or maxItems is negative, minItems is greater than maxItems, the
     * default or example isn't a list, or the XML is an attribute.
     */
    public function __construct(
        SchemaInterface $items,
        ?string $description = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        bool $uniqueItems = false,
        bool $nullable = false,
        ?array $default = null,
        ?array $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ?Xml $xml = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotNegative($minItems, "minItems");
        Validator::assertNotNegative($maxItems, "maxItems");
        Validator::assertNotGreater($minItems, $maxItems, "minItems", "maxItems");
        Validator::assertList($default, "An array schema's default");
        Validator::assertList($example, "An array schema's example");
        Validator::assertXmlFits($xml, Type::ARRAY);
        $this->m_items = $items;
        $this->m_description = $description;
        $this->m_minItems = $minItems;
        $this->m_maxItems = $maxItems;
        $this->m_uniqueItems = $uniqueItems;
        $this->m_nullable = $nullable;
        $this->m_default = $default;
        $this->m_example = $example;
        $this->m_title = $title;
        $this->m_deprecated = $deprecated;
        $this->m_externalDocs = $externalDocs;
        $this->m_xml = $xml;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'type' => Type::ARRAY->value,
            'title' => $this->m_title,
            'description' => $this->m_description,
            'items' => $this->m_items,
            'minItems' => $this->m_minItems,
            'maxItems' => $this->m_maxItems,
            'uniqueItems' => $this->m_uniqueItems ? true : null,
            'default' => $this->m_default,
            'nullable' => $this->m_nullable ? true : null,
            'example' => $this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
            'xml' => $this->m_xml,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
