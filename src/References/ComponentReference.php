<?php

namespace Programster\Swagger\References;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * A reference to a kind of object that can be in the components, so as well as pointing at another document, it can
 * point at a component, and always points at the right part of the components (e.g. "#/components/schemas/").
 */
abstract readonly class ComponentReference extends Reference
{
    /**
     * Create a reference to a component in this document, e.g. SchemaReference::createToComponent("User") for
     * "#/components/schemas/User". It's usually easier to call createReference() on the named component itself
     * (e.g. a NamedSchema), so that the name can't be mistyped.
     *
     * @param string $name - the name of the component.
     *
     * @return static
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public static function createToComponent(string $name) : static
    {
        Validator::assertComponentName($name, "The component name");
        $section = static::getComponentSection();
        return new static("#/components/{$section}/{$name}");
    }


    /**
     * Get the part of the components that this kind of object goes in, e.g. "schemas".
     * @return string
     */
    abstract protected static function getComponentSection() : string;
}
