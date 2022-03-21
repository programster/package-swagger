<?php

namespace Programster\Swagger;

enum ParameterLocation: string
{
    case QUERY = "query";
    case HEADER = "header";
    case PATH = "path";
    case FORM_DATA = "formData";
    case BODY = "body";
}
