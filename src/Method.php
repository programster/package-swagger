<?php

namespace Programster\Swagger;


enum Method: string
{
    case GET = "get";
    case PUT = "put";
    case POST = "post";
    case DELETE = "delete";
    case OPTIONS = "options";
    case HEAD = "head";
    case PATCH = "patch";
    case TRACE = "trace";
}
