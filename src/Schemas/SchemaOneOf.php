<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema that a value matches if it matches exactly one of several schemas (oneOf), e.g. a pet that is either a
 * cat or a dog.
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaOneOf implements SchemaInterface
{
    private SchemaCollection $m_schemas;
    private ?string $m_description;
    private ?Discriminator $m_discriminator;
    private string|int|float|bool|array|\stdClass|null $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * @param SchemaCollection $schemas - the schemas that the value must match exactly one of, or references to them.
     * There must be at least two.
     *
     * @param string|null $description - a description of the value. CommonMark syntax may be used.
     *
     * @param Discriminator|null $discriminator - the property whose value says which schema the value matches. With
     * one, every schema must be a reference, and its mappings must be to those schemas.
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
     * @throws InvalidValueException - if there are fewer than two schemas, or the discriminator doesn't fit them.
     */
    public function __construct(
        SchemaCollection $schemas,
        ?string $description = null,
        ?Discriminator $discriminator = null,
        string|int|float|bool|array|\stdClass|null $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if (count($schemas) < 2)
        {
            $message = "A SchemaOneOf needs at least two schemas, as with one it would be the same as that schema.";
            throw new InvalidValueException($message);
        }

        Validator::assertDiscriminatorFits($discriminator, $schemas);
        $this->m_schemas = clone $schemas;
        $this->m_description = $description;
        $this->m_discriminator = $discriminator;
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
            'oneOf' => $this->m_schemas,
            'discriminator' => $this->m_discriminator,
            'example' => $this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getSchemas() : SchemaCollection { return clone $this->m_schemas; }
}
