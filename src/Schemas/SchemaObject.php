<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\ExternalDocumentation;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A schema for an object, with named properties, or for a map (dictionary) whose values all match one schema.
 * https://swagger.io/specification/v3/#schema-object
 */
final readonly class SchemaObject implements SchemaInterface
{
    private ?string $m_description;
    private PropertyMap $m_properties;
    private ?int $m_minProperties;
    private ?int $m_maxProperties;
    private bool $m_nullable;
    private bool|SchemaInterface $m_additionalProperties;
    private ?Discriminator $m_discriminator;
    private ?array $m_default;
    private ?array $m_example;
    private ?string $m_title;
    private bool $m_deprecated;
    private ?ExternalDocumentation $m_externalDocs;
    private ?Xml $m_xml;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $description - a description of the object. CommonMark syntax may be used.
     *
     * @param PropertyMap $properties - the object's properties. Each says whether it is required.
     *
     * @param int|null $minProperties - the minimum number of properties the object must have.
     *
     * @param int|null $maxProperties - the maximum number of properties the object can have. This can't be fewer
     * than the number of required properties.
     *
     * @param bool $nullable - whether the value may be null as well as an object.
     *
     * @param bool|SchemaInterface $additionalProperties - whether the object can have properties other than the
     * ones in $properties. True (the default) allows any, false allows none, and a schema allows any that match it,
     * e.g. new SchemaObject(additionalProperties: new SchemaInt()) for a map of names to integers.
     *
     * @param Discriminator|null $discriminator - for an object that other schemas extend (with SchemaAllOf), the
     * property that says which of them a value is. It must be one of the properties, and be required.
     *
     * @param array|null $default - the value to assume when none is given, keyed by property name, e.g.
     * ['sort' => "name"].
     *
     * @param array|null $example - an example of the object, keyed by property name, e.g. ['name' => "Bob"].
     *
     * @param string|null $title - a short title for the schema.
     *
     * @param bool $deprecated - true if the value is deprecated, and shouldn't be used.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the value.
     *
     * @param Xml|null $xml - how the object is represented in XML, e.g. the name of its element.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the schema.
     *
     * @throws InvalidValueException - if minProperties or maxProperties is negative, minProperties is greater than
     * maxProperties, maxProperties is fewer than the number of required properties, minProperties is more than the
     * number of properties when no others are allowed, the discriminator's property isn't a required property, the
     * default or example is a list, or the XML is an attribute.
     */
    public function __construct(
        ?string $description = null,
        PropertyMap $properties = new PropertyMap(),
        ?int $minProperties = null,
        ?int $maxProperties = null,
        bool $nullable = false,
        bool|SchemaInterface $additionalProperties = true,
        ?Discriminator $discriminator = null,
        ?array $default = null,
        ?array $example = null,
        ?string $title = null,
        bool $deprecated = false,
        ?ExternalDocumentation $externalDocs = null,
        ?Xml $xml = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotNegative($minProperties, "minProperties");
        Validator::assertNotNegative($maxProperties, "maxProperties");
        Validator::assertNotGreater($minProperties, $maxProperties, "minProperties", "maxProperties");
        $requiredNames = $properties->getRequiredNames();
        $numRequired = count($requiredNames);
        $numProperties = count($properties);

        if ($maxProperties !== null && $maxProperties < $numRequired)
        {
            $message = "maxProperties ({$maxProperties}) can't be fewer than the number of required properties "
                . "({$numRequired}), as no object could then be valid.";

            throw new InvalidValueException($message);
        }

        if ($additionalProperties === false && $minProperties !== null && $minProperties > $numProperties)
        {
            $message = "minProperties ({$minProperties}) can't be more than the number of properties "
                . "({$numProperties}) when additionalProperties is false, as no object could then be valid.";

            throw new InvalidValueException($message);
        }

        if ($discriminator !== null && !in_array($discriminator->getPropertyName(), $requiredNames, true))
        {
            $message = "The discriminator's property, \"{$discriminator->getPropertyName()}\", must be one of the "
                . "object's properties, and be required.";

            throw new InvalidValueException($message);
        }

        Validator::assertNotList($default, "An object schema's default");
        Validator::assertNotList($example, "An object schema's example");
        Validator::assertXmlFits($xml, Type::OBJECT);
        $this->m_description = $description;
        $this->m_properties = clone $properties;
        $this->m_minProperties = $minProperties;
        $this->m_maxProperties = $maxProperties;
        $this->m_nullable = $nullable;
        $this->m_additionalProperties = $additionalProperties;
        $this->m_discriminator = $discriminator;
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
        $requiredNames = $this->m_properties->getRequiredNames();

        $fields = [
            'type' => Type::OBJECT->value,
            'title' => $this->m_title,
            'description' => $this->m_description,
            'properties' => $this->m_properties->isEmpty() ? null : $this->m_properties,
            'required' => count($requiredNames) === 0 ? null : $requiredNames,
            'additionalProperties' => ($this->m_additionalProperties === true) ? null : $this->m_additionalProperties,
            'discriminator' => $this->m_discriminator,
            'minProperties' => $this->m_minProperties,
            'maxProperties' => $this->m_maxProperties,
            'default' => ($this->m_default === null) ? null : (object)$this->m_default,
            'nullable' => $this->m_nullable ? true : null,
            'example' => ($this->m_example === null) ? null : (object)$this->m_example,
            'deprecated' => $this->m_deprecated ? true : null,
            'externalDocs' => $this->m_externalDocs,
            'xml' => $this->m_xml,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getProperties() : PropertyMap { return clone $this->m_properties; }
}
