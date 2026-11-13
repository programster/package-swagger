<?php

namespace Programster\Swagger\Schemas;


/**
 * The formats that OpenAPI defines for number schemas (SchemaNumber).
 */
enum NumberFormat: string
{
    // A single precision floating point number.
    case FLOAT = "float";

    // A double precision floating point number.
    case DOUBLE = "double";
}
