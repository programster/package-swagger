<?php

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Schemas\SchemaInterface;


/**
 * A header, of a response or an encoding, which is given its name by a MappedHeader (or a NamedHeader, in the
 * components). Create one with createFromSchema() for most headers, or createFromContentMap() for one whose value is
 * in a format such as JSON.
 * https://swagger.io/specification/v3/#header-object
 */
final readonly class Header implements HeaderInterface
{
    private ?SchemaInterface $m_schema;
    private ?ContentMap $m_content;
    private bool $m_required;
    private ?string $m_description;
    private bool $m_deprecated;
    private ?bool $m_explode;
    private ValueExample|ExampleMap|null $m_example;
    private ExtensionMap $m_extensions;


    /**
     * @throws InvalidValueException - if the content has more than one media type.
     */
    private function __construct(
        ?SchemaInterface $schema,
        ?ContentMap $content,
        bool $required,
        ?string $description,
        bool $deprecated,
        ?bool $explode,
        ValueExample|ExampleMap|null $example,
        ExtensionMap $extensions
    )
    {
        if ($content !== null && count($content) !== 1)
        {
            throw new InvalidValueException("A header's content must have exactly one media type.");
        }

        $this->m_schema = $schema;
        $this->m_content = ($content === null) ? null : clone $content;
        $this->m_required = $required;
        $this->m_description = $description;
        $this->m_deprecated = $deprecated;
        $this->m_explode = $explode;
        $this->m_example = ($example instanceof ExampleMap) ? clone $example : $example;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Create a header whose value is described by a schema.
     *
     * @param SchemaInterface $schema - the schema of the header's value, or a reference to one.
     *
     * @param bool $required - true if the header is always sent.
     *
     * @param string|null $description - a description of the header. CommonMark syntax may be used.
     *
     * @param bool $deprecated - true if the header is deprecated, and shouldn't be relied on.
     *
     * @param bool|null $explode - for an array or object value, whether to send it as separate values.
     *
     * @param ValueExample|ExampleMap|null $example - an example of the value, or a map of named examples.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the header.
     *
     * @return Header
     */
    public static function createFromSchema(
        SchemaInterface $schema,
        bool $required = false,
        ?string $description = null,
        bool $deprecated = false,
        ?bool $explode = null,
        ValueExample|ExampleMap|null $example = null,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Header
    {
        return new Header($schema, null, $required, $description, $deprecated, $explode, $example, $extensions);
    }


    /**
     * Create a header whose value is in a format such as JSON, described by a media type.
     *
     * @param ContentMap $content - the media type of the header's value. It must have exactly one.
     *
     * @param bool $required - true if the header is always sent.
     *
     * @param string|null $description - a description of the header. CommonMark syntax may be used.
     *
     * @param bool $deprecated - true if the header is deprecated, and shouldn't be relied on.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the header.
     *
     * @return Header
     *
     * @throws InvalidValueException - if the content has more than one media type.
     */
    public static function createFromContentMap(
        ContentMap $content,
        bool $required = false,
        ?string $description = null,
        bool $deprecated = false,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Header
    {
        return new Header(null, $content, $required, $description, $deprecated, null, null, $extensions);
    }


    public function jsonSerialize() : \stdClass
    {
        $isExampleMap = $this->m_example instanceof ExampleMap;

        $fields = [
            'description' => $this->m_description,
            'required' => $this->m_required ? true : null,
            'deprecated' => $this->m_deprecated ? true : null,
            'explode' => $this->m_explode,
            'schema' => $this->m_schema,
            'content' => $this->m_content,
            'example' => $isExampleMap ? null : $this->m_example,
            'examples' => $isExampleMap ? $this->m_example : null,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
