<?php

namespace Programster\Swagger\Exceptions;


/**
 * Thrown when objects that are each valid on their own don't fit together, such as a security requirement that
 * names a security scheme that isn't in the components, or a path whose template has a parameter that isn't defined.
 */
final class InvalidDocumentException extends SwaggerException
{
}
