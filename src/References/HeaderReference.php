<?php

namespace Programster\Swagger\References;

use Programster\Swagger\HeaderInterface;


/**
 * A reference to a header, which can be used wherever one can.
 */
final readonly class HeaderReference extends ComponentReference implements HeaderInterface
{
    protected static function getComponentSection() : string
    {
        return "headers";
    }
}
