<?php

namespace Programster\Swagger\References;


/**
 * A reference to an example, which can be given to a NamedExample in place of the example itself.
 */
final readonly class ExampleReference extends ComponentReference
{
    protected static function getComponentSection() : string
    {
        return "examples";
    }
}
