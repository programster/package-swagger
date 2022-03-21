<?php

/*
 * An "enum" for the type a parameter/property can be.
 */

namespace Programster\Swagger;

enum Type: string
{
    case INT = "integer";
    case STRING = "string";
    case BOOL = "boolean";
    case NUMBER = "number";
}
