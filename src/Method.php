<?php

namespace Programster\Swagger;


enum Method: string
{
    case GET = "get";
    case POST = "post";
    case PUT = "put";
    case PATCH = "patch";
    case DELETE = "delete";
}
