<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;


/**
 * A schema that any value matches, whatever its type, e.g. for a field whose value is up to the client.
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaAnyValue implements SchemaInterface
{
    private ?string $m_description;
    private string|int|float|bool|array|\stdClass|null $m_default;
    private string|int|float|bool|array|\stdClass|null $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param string|int|float|bool|array|\stdClass|null $default - the value to assume when none is given. An array
     * with keys is output as an object, and a list as an array. Use a \stdClass for an empty object.
     *
     * @param string|int|float|bool|array|\stdClass|null $example - an example of the value, which is output in the
     * same way as the default.
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
        ?string $description = null,
        string|int|float|bool|array|\stdClass|null $default = null,
        string|int|float|bool|array|\stdClass|null $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $this->m_description = $description;
        $this->m_default = is_object($default) ? clone $default : $default;
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
            'default' => $this->m_default,
            'example' => $this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
