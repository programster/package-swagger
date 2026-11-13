<?php

namespace Programster\Swagger;


/**
 * Common mime types (also known as media types), for the content of requests and responses. Anything that takes one
 * also takes a string, for a mime type that isn't here, e.g. "application/vnd.api+json".
 */
enum MimeType: string
{
    case JSON = "application/json";
    case XML = "application/xml";
    case PDF = "application/pdf";
    case ZIP = "application/zip";
    case BINARY = "application/octet-stream";
    case FORM_URL_ENCODED = "application/x-www-form-urlencoded";
    case MULTIPART_FORM_DATA = "multipart/form-data";
    case PLAIN_TEXT = "text/plain";
    case HTML = "text/html";
    case CSV = "text/csv";
    case PNG = "image/png";
    case JPEG = "image/jpeg";
    case GIF = "image/gif";
    case SVG = "image/svg+xml";
    case IMAGE = "image/*";
    case ANY = "*/*";
}
