<?php

namespace Programster\Swagger\References;

use Programster\Swagger\RequestBodyInterface;


/**
 * A reference to a request body, which can be used wherever one can.
 */
final readonly class RequestBodyReference extends ComponentReference implements RequestBodyInterface
{
    protected static function getComponentSection() : string
    {
        return "requestBodies";
    }
}
