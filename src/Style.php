<?php

/*
 * An enum for the various possible style values
 * https://swagger.io/specification/#style-values
 */


namespace Programster\Swagger;


enum Style : string
{
    /*
     * Path-style parameters defined by RFC6570
     */
    case MATRIX = "matrix";

    /*
     * Label style parameters defined by RFC6570
     */
    case LABEL = "label";

    /*
     * Form style parameters defined by RFC6570. This option replaces collectionFormat with a csv (when explode is
     * false) or multi (when explode is true) value from OpenAPI 2.0.
     */
    case FORM = "form";

    /*
     * Simple style parameters defined by RFC6570. This option replaces collectionFormat with a csv value from
     * OpenAPI 2.0.
     */
    case SIMPLE = "simple";

    /*
     * Space separated array values. This option replaces collectionFormat equal to ssv from OpenAPI 2.0.
     */
    case SPACE_DELIMITED = "spaceDelimited";

    /*
     * Pipe separated array values. This option replaces collectionFormat equal to pipes from OpenAPI 2.0.
     */
    case PIPE_DELIMITED = "pipeDelimited";

    /*
     * Provides a simple way of rendering nested objects using form parameters.
     */
    case DEEP_OBJECT = "deepObject";

}
