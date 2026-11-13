<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema for a boolean.
 * https://swagger.io/specification/v3/#data-types
 */
final readonly class SchemaBool implements SchemaInterface
{
    private ?string $m_description;
    private ?bool $m_default;
    private bool $m_nullable;
    private ?bool $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ?Xml $m_xml;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param bool|null $default - the value to assume when none is given.
     *
     * @param bool $nullable - whether the value may be null as well.
     *
     * @param bool|null $example - an example of the value.
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
     * @throws InvalidValueException - if the XML is wrapped.
     */
    public function __construct(
        ?string $description = null,
        ?bool $default = null,
        bool $nullable = false,
        ?bool $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ?Xml $xml = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertXmlFits($xml, Type::BOOL);
        $this->m_description = $description;
        $this->m_default = $default;
        $this->m_nullable = $nullable;
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
            'type' => Type::BOOL->value,
            'title' => $this->m_title,
            'description' => $this->m_description,
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
