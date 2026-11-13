<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;


/**
 * A schema that a value matches if it doesn't match another schema (not).
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaNot implements SchemaInterface
{
    private SchemaInterface $m_schema;
    private ?string $m_description;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * @param SchemaInterface $schema - the schema that the value must not match, or a reference to one.
     *
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param string|null $title - a short title for the schema.
     *
     * @param bool $deprecated - true if the value is deprecated, and shouldn't be used.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the value.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the schema.
     */
    public function __construct(
        SchemaInterface $schema,
        ?string $description = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $this->m_schema = $schema;
        $this->m_description = $description;
        $this->m_title = $title;
        $this->m_deprecated = $deprecated;
        $this->m_externalDocs = $externalDocs;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'title' => $this->m_title,
            'description' => $this->m_description,
            'not' => $this->m_schema,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
