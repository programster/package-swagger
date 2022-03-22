<?php

namespace Programster\Swagger;

// https://swagger.io/specification/#parameter-locations

enum ParameterLocation: string
{
    case PATH = "path";
    case QUERY = "query";
    case HEADER = "header";
    case COOKIE = "cookie";
}
