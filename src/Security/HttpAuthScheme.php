<?php

namespace Programster\Swagger\Security;


/**
 * The HTTP authentication schemes (from the IANA registry) that an HttpAuth security scheme can use. Basic and bearer
 * aren't here, as they have their own security schemes: HttpBasicAuth, and HttpBearer (or JwtBearer).
 * https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml
 */
enum HttpAuthScheme: string
{
    case CONCEALED = "concealed";
    case DIGEST = "digest";
    case DPOP = "dpop";
    case GNAP = "gnap";
    case HOBA = "hoba";
    case MUTUAL = "mutual";
    case NEGOTIATE = "negotiate";
    case OAUTH = "oauth";
    case PRIVATE_TOKEN = "privatetoken";
    case SCRAM_SHA_1 = "scram-sha-1";
    case SCRAM_SHA_256 = "scram-sha-256";
    case VAPID = "vapid";
}
