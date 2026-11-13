<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;


/**
 * A schema that a value matches if it matches all of several schemas (allOf), e.g. to extend a schema with more
 * properties. It can also have just one schema, e.g. to describe a reference differently where it is used.
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaAllOf implements SchemaInterface
{
    private SchemaCollection $m_schemas;
    private ?string $m_description;
    private string|int|float|bool|array|\stdClass|null $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * @param SchemaCollection $schemas - the schemas that the value must all match, or references to them. There must
     * be at least one.
     *
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param string|int|float|bool|array|\stdClass|null $example - an example of the value. An array with keys is
     * output as an object, and a list as an array. Use a \stdClass for an empty object.
     *
     * @param string|null $title - a short title for the schema.
     *
     * @param bool $deprecated - true if the value is deprecated, and shouldn't be used.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the value.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the schema.
     *
     * @throws InvalidValueException - if there are no schemas.
     */
    public function __construct(
        SchemaCollection $schemas,
        ?string $description = null,
        string|int|float|bool|array|\stdClass|null $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if (count($schemas) < 1)
        {
            $message = "A SchemaAllOf needs at least one schema.";
            throw new InvalidValueException($message);
        }

        $this->m_schemas = clone $schemas;
        $this->m_description = $description;
        $this->m_example = is_object($example) ? clone $example : $example;
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
            'allOf' => $this->m_schemas,
            'example' => $this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getSchemas() : SchemaCollection { return clone $this->m_schemas; }
}
