<?php

namespace Programster\Swagger\Schemas;


/**
 * The formats that OpenAPI defines for integer schemas (SchemaInt).
 */
enum IntegerFormat: string
{
    // A signed 32 bit integer.
    case INT32 = "int32";

    // A signed 64 bit integer, also known as a long.
    case INT64 = "int64";
}
