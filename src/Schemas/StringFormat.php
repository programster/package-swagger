<?php

/*
 * Formats for string schemas (SchemaString). OpenAPI defines date, date-time, password, byte and binary, and
 * allows any other format, such as the ones from JSON Schema below.
 * http://json-schema.org/understanding-json-schema/reference/string.html#built-in-formats
 */

namespace Programster\Swagger\Schemas;


enum StringFormat: string
{
    // Date and time together, for example, 2018-11-13T20:20:39+00:00.
    case DATE_TIME = "date-time";

    // Time, for example, 20:20:39+00:00
    case TIME = "time";

    // Date, for example, 2018-11-13.
    case DATE = "date";

    // A duration as defined by the ISO 8601 ABNF for “duration”. For example, P3D expresses a duration of 3 days.
    case DURATION = "duration";

    // Internet email address, see RFC 5321, section 4.1.2.
    case EMAIL = "email";

    // The internationalized form of an Internet email address, see RFC 6531.
    case IDN_EMAIL = "idn-email";

    // Internet host name, see RFC 1123, section 2.1.
    case HOSTNAME = "hostname";

    // An internationalized Internet host name, see RFC5890, section 2.3.2.3.
    case IDN_HOSTNAME = "idn-hostname";

    // IPv4 address, according to dotted-quad ABNF syntax as defined in RFC 2673, section 3.2.
    case IPV4 = "ipv4";

    // IPv6 address, as defined in RFC 2373, section 2.2.
    case IPV6 = "ipv6";

    // A Universally Unique Identifier as defined by RFC 4122. Example: 3e4666bf-d5e5-4aa7-b8ce-cefe41c7568a
    case UUID = "uuid";

    // A universal resource identifier (URI), according to RFC3986.
    case URI = "uri";

    // A URI Reference (either a URI or a relative-reference), according to RFC3986, section 4.1.
    case URI_REFERENCE = "uri-reference";

    // The internationalized equivalent of a “uri”, according to RFC3987.
    case IRI = "iri";

    // The internationalized equivalent of a “uri-reference”, according to RFC3987
    case IRI_REFERENCE = "iri-reference";

    // A URI Template (of any level) according to RFC6570. If you don’t already know what a URI Template is, you
    // probably don’t need this value.
    case URI_TEMPLATE = "uri-template";

    // A JSON Pointer, according to RFC6901. There is more discussion on the use of JSON Pointer within JSON Schema in
    // Structuring a complex schema. Note that this should be used only when the entire string contains only JSON
    // Pointer content, e.g. /foo/bar. JSON Pointer URI fragments, e.g. #/foo/bar/ should use "uri-reference".
    case JSON_POINTER = "json-pointer";

    // A relative JSON pointer: https://tools.ietf.org/html/draft-handrews-relative-json-pointer-01
    case RELATIVE_JSON_POINTER = "relative-json-pointer";

    // A regular expression, which should be valid according to the ECMA 262 dialect.
    case REGEX = "regex";

    // A hint to UIs to hide the input, e.g. for a password. Defined by OpenAPI.
    case PASSWORD = "password";

    // Base64 encoded characters. Defined by OpenAPI.
    case BYTE = "byte";

    // Any sequence of octets, e.g. the contents of a file. Defined by OpenAPI.
    case BINARY = "binary";
}
