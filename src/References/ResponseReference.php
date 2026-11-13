<?php

namespace Programster\Swagger\References;

use Programster\Swagger\ResponseInterface;


/**
 * A reference to a response, which can be used wherever one can.
 */
final readonly class ResponseReference extends ComponentReference implements ResponseInterface
{
    protected static function getComponentSection() : string
    {
        return "responses";
    }
}
