<?php

namespace Programster\Swagger\Exceptions;


/**
 * Thrown when two items with the same key (e.g. two schemas with the same name) are put into a collection whose
 * items must have unique keys.
 */
final class DuplicateKeyException extends SwaggerException
{
}
