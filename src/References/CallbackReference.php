<?php

namespace Programster\Swagger\References;

use Programster\Swagger\CallbackInterface;


/**
 * A reference to a callback, which can be used wherever one can.
 */
final readonly class CallbackReference extends ComponentReference implements CallbackInterface
{
    protected static function getComponentSection() : string
    {
        return "callbacks";
    }
}
