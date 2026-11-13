<?php

namespace Programster\Swagger\References;

use Programster\Swagger\ParameterInterface;


/**
 * A reference to a parameter, which can be used wherever one can.
 */
final readonly class ParameterReference extends ComponentReference implements ParameterInterface
{
    protected static function getComponentSection() : string
    {
        return "parameters";
    }
}
