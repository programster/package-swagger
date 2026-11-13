<?php

namespace Programster\Swagger\References;

use Programster\Swagger\PathItemInterface;


/**
 * A reference to a path item in another document, which can be used for a path or a callback in place of a PathItem.
 * OpenAPI 3.0 has no path items in the components, so this can only point at another document.
 */
final readonly class PathItemReference extends Reference implements PathItemInterface
{
}
