<?php

/*
 * A mime type (also known as "Media type"
 */

namespace Programster\Swagger;


enum MimeType: string
{
    case JSON = "application/json";
    case PLAIN_TEXT = "text/plain";
    case BINARY = "application/octet-stream";
    case IMAGE = "image/*";
    case MULTIPART_FORM_DATA = "multipart/form-data";
    case FORM_URL_ENCODED = "application/x-www-form-urlencoded";
}
