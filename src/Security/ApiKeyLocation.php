<?php

namespace Programster\Swagger\Security;


/**
 * Where an API key (ApiKey) can be sent. Unlike a parameter, it can't be part of the path.
 */
enum ApiKeyLocation: string
{
    case QUERY = "query";
    case HEADER = "header";
    case COOKIE = "cookie";
}
