<?php

namespace Programster\Swagger;

enum Type: string
{
    case STRING = "string";
    case INT = "integer";
    case NUMBER = "number";
    case BOOL = "boolean";
    case ARRAY = "array";
    case OBJECT = "object";
    case NULL = "null";
}
