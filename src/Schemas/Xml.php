<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * How a schema's value is represented in XML, e.g. under another element name, or as an attribute.
 * https://swagger.io/specification/v3/#xml-object
 */
final readonly class Xml implements \JsonSerializable
{
    private ?string $m_name;
    private ?string $m_namespace;
    private ?string $m_prefix;
    private bool $m_attribute;
    private bool $m_wrapped;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $name - the name of the element (or attribute), instead of the property's name. For an
     * array, this is the name of the wrapping element, and only has an effect when $wrapped is true.
     *
     * @param string|null $namespace - the URI of the namespace, which must be absolute, e.g.
     * "https://example.com/schema".
     *
     * @param string|null $prefix - the prefix to use for the name. This needs a namespace.
     *
     * @param bool $attribute - true to output the value as an attribute, rather than an element. This can't be used
     * for an array or an object.
     *
     * @param bool $wrapped - true to wrap an array's items in an element (e.g. <books><book/><book/></books>). This
     * can only be used for an array.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the XML object.
     *
     * @throws InvalidValueException - if nothing is given, the name is empty, the namespace isn't an absolute URI, or
     * there's a prefix without a namespace.
     */
    public function __construct(
        ?string $name = null,
        ?string $namespace = null,
        ?string $prefix = null,
        bool $attribute = false,
        bool $wrapped = false,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if (
               $name === null
            && $namespace === null
            && $prefix === null
            && !$attribute
            && !$wrapped
            && $extensions->isEmpty()
        )
        {
            throw new InvalidValueException("An Xml needs at least one option, as without any it has no effect.");
        }

        if ($name !== null)
        {
            Validator::assertNotEmpty($name, "The XML name");
        }

        if ($namespace !== null && preg_match('/^[a-zA-Z][a-zA-Z0-9+.\-]*:./', $namespace) !== 1)
        {
            throw new InvalidValueException("The XML namespace must be an absolute URI, but was \"{$namespace}\".");
        }

        if ($prefix !== null && $namespace === null)
        {
            throw new InvalidValueException("The XML prefix \"{$prefix}\" needs a namespace for it to stand for.");
        }

        $this->m_name = $name;
        $this->m_namespace = $namespace;
        $this->m_prefix = $prefix;
        $this->m_attribute = $attribute;
        $this->m_wrapped = $wrapped;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'name' => $this->m_name,
            'namespace' => $this->m_namespace,
            'prefix' => $this->m_prefix,
            'attribute' => $this->m_attribute ? true : null,
            'wrapped' => $this->m_wrapped ? true : null,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function isAttribute() : bool { return $this->m_attribute; }
    public function isWrapped() : bool { return $this->m_wrapped; }
}
