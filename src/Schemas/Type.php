<?php

namespace Programster\Swagger\Schemas;


/**
 * The types that a schema can have in OpenAPI 3.0. Each schema class (e.g. SchemaString) sets its own, so this only
 * needs to be used to read a schema's type.
 */
enum Type: string
{
    case STRING = "string";
    case INT = "integer";
    case NUMBER = "number";
    case BOOL = "boolean";
    case ARRAY = "array";
    case OBJECT = "object";
}
