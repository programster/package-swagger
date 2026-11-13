<?php

namespace Programster\Swagger\Schemas;


/**
 * Whether a property of an object is sent in requests, responses or both, e.g. READ_ONLY for an ID that the server
 * sets.
 */
enum Access
{
    // The property can be in both requests and responses.
    case READ_WRITE;

    // The property is only in responses (readOnly). If it is required, it is only required in responses.
    case READ_ONLY;

    // The property is only in requests (writeOnly), e.g. a password. If it is required, it is only required in
    // requests.
    case WRITE_ONLY;
}
