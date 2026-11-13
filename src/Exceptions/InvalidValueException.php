<?php

namespace Programster\Swagger\Exceptions;


/**
 * Thrown when an object is created with a value it can't accept, such as a URL that isn't a URL, or a minimum
 * that is greater than its maximum. It's thrown by the constructor (or creator method), so it points at the line
 * that made the mistake.
 */
final class InvalidValueException extends SwaggerException
{
}
