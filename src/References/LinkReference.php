<?php

namespace Programster\Swagger\References;

use Programster\Swagger\LinkInterface;


/**
 * A reference to a link, which can be used wherever one can.
 */
final readonly class LinkReference extends ComponentReference implements LinkInterface
{
    protected static function getComponentSection() : string
    {
        return "links";
    }
}
