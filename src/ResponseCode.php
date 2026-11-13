<?php

namespace Programster\Swagger;


/**
 * The response codes that an operation's responses can be for: the HTTP status codes, ranges of them (e.g. 4XX for
 * any client error), and DEFAULT for any code that the operation has no other response for. A MappedResponse can
 * also be given a code that isn't here as an int, e.g. 499.
 * https://swagger.io/specification/v3/#responses-object
 */
enum ResponseCode : string
{
    /**
     * Any response code that the operation doesn't have another response for, e.g. for a generic error response.
     */
    case DEFAULT = "default";


    /**
     * Any informational (1XX) response code that the operation doesn't have a response of its own for.
     */
    case ANY_INFORMATIONAL = "1XX";


    /**
     * Any successful (2XX) response code that the operation doesn't have a response of its own for.
     */
    case ANY_SUCCESS = "2XX";


    /**
     * Any redirection (3XX) response code that the operation doesn't have a response of its own for.
     */
    case ANY_REDIRECTION = "3XX";


    /**
     * Any client error (4XX) response code that the operation doesn't have a response of its own for.
     */
    case ANY_CLIENT_ERROR = "4XX";


    /**
     * Any server error (5XX) response code that the operation doesn't have a response of its own for.
     */
    case ANY_SERVER_ERROR = "5XX";


    /**
     * The server has received the request headers, and the client should go on to send the body.
     */
    case CONTINUE = "100";


    /**
     * The server is switching to the protocol that the client asked for in its Upgrade header.
     */
    case SWITCHING_PROTOCOLS = "101";


    /**
     * The server has received the request and is processing it, but no response is available yet. (WebDAV)
     */
    case PROCESSING = "102";


    /**
     * Some response headers are sent before the final response, so that the client can start preloading resources.
     */
    case EARLY_HINTS = "103";


    /**
     * Standard response for successful HTTP requests. The actual response will depend on the
     * request method used. In a GET request, the response will contain an entity corresponding
     * to the requested resource. In a POST request, the response will contain an entity describing
     * or containing the result of the action.
     */
    case OK = "200";


    /**
     * The request has been fulfilled, resulting in the creation of a new resource.
     */
    case CREATED = "201";


    /**
     * The request has been accepted for processing, but the processing has not been completed.
     * The request might or might not be eventually acted upon, and may be disallowed when
     * processing occurs.
     */
    case ACCEPTED = "202";


    /**
     * The server successfully processed the request, but is returning information that may be from another source,
     * as it was modified by a transforming proxy.
     */
    case NON_AUTHORITATIVE_INFORMATION = "203";


    /**
     * The server successfully processed the request and is not returning any content.
     */
    case NO_CONTENT = "204";


    /**
     * The server successfully processed the request, but is not returning any content.
     * Unlike a 204 response, this response requires that the requester reset the document view.
     */
    case RESET_CONTENT = "205";


    /**
     * The server is delivering only part of the resource (byte serving) due to a range header sent
     * by the client. The range header is used by HTTP clients to enable resuming of interrupted
     * downloads, or split a download into multiple simultaneous streams.
     */
    case PARTIAL_CONTENT = "206";


    /**
     * The message body that follows is by default an XML message and can contain a number of
     * separate response codes, depending on how many sub-requests were made.
     */
    case MULTI_STATUS = "207";


    /**
     * The members of a DAV binding have already been enumerated in a preceding part of the
     * (multistatus) response, and are not being included again.
     */
    case ALREADY_REPORTED = "208";


    /**
     * The server has fulfilled a request for the resource, and the response is a representation
     * of the result of one or more instance-manipulations applied to the current instance.
     */
    case IM_USED = "226";


    /**
     * Indicates multiple options for the resource from which the client may choose
     * (via agent-driven content negotiation). For example, this code could be used to present
     * multiple video format options, to list files with different filename extensions, or to
     * suggest word-sense disambiguation.
     */
    case MULTIPLE_CHOICES = "300";


    /**
     * This and all future requests should be directed to the given URI.
     */
    case MOVED_PERMANENTLY = "301";


    /**
     * Tells the client to look at (browse to) another URL. The HTTP/1.0 specification (RFC 1945)
     * required the client to perform a temporary redirect with the same method (the original
     * describing phrase was "Moved Temporarily"),[21] but popular browsers implemented 302
     * redirects by changing the method to GET. Therefore, HTTP/1.1 added status codes 303 and
     * 307 to distinguish between the two behaviours.[22]
     */
    case FOUND = "302";


    /**
     * The response to the request can be found under another URI using the GET method.
     * When received in response to a POST (or PUT/DELETE), the client should presume that the
     * server has received the data and should issue a new GET request to the given URI.
     */
    case SEE_OTHER = "303";


    /**
     * Indicates that the resource has not been modified since the version specified by the
     * request headers If-Modified-Since or If-None-Match. In such case, there is no need to
     * retransmit the resource since the client still has a previously-downloaded copy.
     */
    case NOT_MODIFIED = "304";


    /**
     * The requested resource is available only through a proxy, the address for which is provided
     * in the response. Many HTTP clients (such as Mozilla and Internet Explorer) do not
     * correctly handle responses with this status code, primarily for security reasons.
     */
    case USE_PROXY = "305";


    /**
     * No longer used. Originally meant "Subsequent requests should use the specified proxy."
     */
    case SWITCH_PROXY = "306";


    /**
     * In this case, the request should be repeated with another URI; however, future requests
     * should still use the original URI. In contrast to how 302 was historically implemented,
     * the request method is not allowed to be changed when reissuing the original request.
     * For example, a POST request should be repeated using another POST request.
     */
    case TEMPORARY_REDIRECT = "307";


    /**
     * The request and all future requests should be repeated using another URI.
     * 307 and 308 parallel the behaviors of 302 and 301, but do not allow the HTTP method to
     * change. So, for example, submitting a form to a permanently redirected resource may
     * continue smoothly.
     */
    case PERMANENT_REDIRECT = "308";


    /**
     * The server cannot or will not process the request due to an apparent client error
     * (e.g., malformed request syntax, size too large, invalid request message framing, or
     * deceptive request routing).
     */
    case BAD_REQUEST = "400";


    /**
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has
     * failed or has not yet been provided. The response must include a WWW-Authenticate header
     * field containing a challenge applicable to the requested resource. See Basic access
     * authentication and Digest access authentication. 401 semantically means
     * "unauthenticated", i.e. the user does not have the necessary credentials.
     * Note: Some sites incorrectly issue HTTP 401 when an IP address is banned from the website
     * (usually the website domain) and that specific address is refused permission to access a
     * website.
     */
    case UNAUTHORIZED = "401";


    /**
     * Reserved for future use. The original intention was that this code might be used as part
     * of some form of digital cash or micropayment scheme, as proposed for example by GNU Taler,
     * but that has not yet happened, and this code is not usually used. Google Developers API
     * uses this status if a particular developer has exceeded the daily limit on requests.
     * Sipgate uses this code if an account does not have sufficient funds to start a call.
     *  Shopify uses this code when the store has not paid their fees and is temporarily disabled.
     */
    case PAYMENT_REQUIRED = "402";


    /**
     * The request was valid, but the server is refusing action. The user might not have the
     * necessary permissions for a resource, or may need an account of some sort.
     */
    case FORBIDDEN = "403";


    /**
     * The requested resource could not be found but may be available in the future.
     * Subsequent requests by the client are permissible.
     */
    case NOT_FOUND = "404";


    /**
     * A request method is not supported for the requested resource; for example, a GET request on
     * a form that requires data to be presented via POST, or a PUT request on a read-only resource.
     */
    case METHOD_NOT_ALLOWED = "405";


    /**
     * The requested resource is capable of generating only content not acceptable according to
     * the Accept headers sent in the request. See Content negotiation:
     * https://en.wikipedia.org/wiki/Content_negotiation
     */
    case NOT_ACCEPTABLE = "406";


    /**
     * The client must first authenticate itself with the proxy.
     */
    case PROXY_AUTHENTICATION_REQUIRED = "407";


    /**
     * The server timed out waiting for the request. According to HTTP specifications:
     * "The client did not produce a request within the time that the server was prepared to wait.
     * The client MAY repeat the request without modifications at any later time."
     */
    case REQUEST_TIMEOUT = "408";


    /**
     * Indicates that the request could not be processed because of conflict in the current state
     * of the resource, such as an edit conflict between multiple simultaneous updates.
     */
    case CONFLICT = "409";


    /**
     * Indicates that the resource requested is no longer available and will not be available again.
     * This should be used when a resource has been intentionally removed and the resource should
     * be purged. Upon receiving a 410 status code, the client should not request the resource in
     * the future. Clients such as search engines should remove the resource from their indices.
     * Most use cases do not require clients and search engines to purge the resource, and a
     * "404 Not Found" may be used instead.
     */
    case GONE = "410";


    /**
     * The request did not specify the length of its content, which is required by the requested
     * resource.
     */
    case LENGTH_REQUIRED = "411";


    /**
     * The server does not meet one of the preconditions that the requester put on the request.
     */
    case PRECONDITION_FAILED = "412";


    /**
     * The request is larger than the server is willing or able to process.
     * Previously called "Request Entity Too Large".
     */
    case PAYLOAD_TOO_LARGE = "413";


    /**
     * The URI provided was too long for the server to process.
     * Often the result of too much data being encoded as a query-string of a GET request, in which
     * case it should be converted to a POST request. Called "Request-URI Too Long" previously.
     */
    case URI_TOO_LONG = "414";


    /**
     * The request entity has a media type which the server or resource does not support.
     * For example, the client uploads an image as image/svg+xml, but the server requires that
     * images use a different format.
     */
    case UNSUPPORTED_MEDIA_TYPE = "415";


    /**
     * The client has asked for a portion of the file (byte serving), but the server cannot supply
     * that portion. For example, if the client asked for a part of the file that lies beyond the
     * end of the file. Called "Requested Range Not Satisfiable" previously.
     */
    case RANGE_NOT_SATISFIABLE = "416";


    /**
     * The server cannot meet the requirements of the Expect request-header field.
     */
    case EXPECTATION_FAILED = "417";


    /**
     * This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324,
     * Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP
     * servers. The RFC specifies this code should be returned by teapots requested to brew coffee.
     *  This HTTP status is used as an Easter egg in some websites, including Google.com.
     */
    case IM_A_TEAPOT = "418";


    /**
     * The request was directed at a server that is not able to produce a response
     * (for example because of connection reuse).
     */
    case MISDIRECTED_REQUEST = "421";


    /**
     * The request was well-formed but was unable to be followed due to semantic errors.
     */
    case UNPROCESSABLE_ENTITY = "422";


    /**
     * The resource that is being accessed is locked. WebDAV)
     */
    case LOCKED = "423";


    /**
     * The request failed because it depended on another request and that request failed
     * (e.g., a PROPPATCH).
     */
    case FAILED_DEPENDENCY = "424";


    /**
     * The client should switch to a different protocol such as TLS/1.0, given in the Upgrade
     * header field.
     */
    case UPGRADE_REQUIRED = "426";


    /**
     * The origin server requires the request to be conditional.
     * Intended to prevent the 'lost update' problem, where a client GETs a resource's state,
     * modifies it, and PUTs it back to the server, when meanwhile a third party has modified the
     * state on the server, leading to a conflict."
     */
    case PRECONDITION_REQUIRED = "428";


    /**
     * The user has sent too many requests in a given amount of time. Intended for use with
     * rate-limiting schemes.
     */
    case TOO_MANY_REQUESTS = "429";


    /**
     * The server is unwilling to process the request because either an individual header field,
     * or all the header fields collectively, are too large.
     */
    case REQUEST_HEADER_FIELDS_TOO_LARGE = "431";


    /**
     * A server operator has received a legal demand to deny access to a resource or to a set of
     * resources that includes the requested resource. The code 451 was chosen as a reference
     * to the novel Fahrenheit 451 (see the Acknowledgements in the RFC).
     */
    case UNAVAILABLE_FOR_LEGAL_REASONS = "451";


    /**
     * A generic error message, given when an unexpected condition was encountered and no more
     * specific message is suitable.
     */
    case INTERNAL_SERVER_ERROR = "500";


    /**
     * The server either does not recognize the request method, or it lacks the ability to
     * fulfil the request. Usually this implies future availability (e.g., a new feature of a
     * web-service API).
     */
    case NOT_IMPLEMENTED = "501";


    /**
     * The server was acting as a gateway or proxy and received an invalid response from the
     * upstream server.
     */
    case BAD_GATEWAY = "502";


    /**
     * The server is currently unavailable (because it is overloaded or down for maintenance).
     * Generally, this is a temporary state.
     */
    case SERVICE_UNAVAILABLE = "503";


    /**
     * The server was acting as a gateway or proxy and did not receive a timely response from
     * the upstream server.
     */
    case GATEWAY_TIMEOUT = "504";


    /**
     * The server does not support the HTTP protocol version used in the request.
     */
    case HTTP_VERSION_NOT_SUPPORTED = "505";


    /**
     * Transparent content negotiation for the request results in a circular reference.
     */
    case VARIANT_ALSO_NEGOTIATES = "506";


    /**
     * The server is unable to store the representation needed to complete the request.
     */
    case INSUFFICIENT_STORAGE = "507";


    /**
     * The server detected an infinite loop while processing the request (sent in lieu of 208
     * Already Reported).
     */
    case LOOP_DETECTED = "508";


    /**
     * Further extensions to the request are required for the server to fulfill it.
     */
    case NOT_EXTENDED = "510";


    /**
     * The client needs to authenticate to gain network access.
     * Intended for use by intercepting proxies used to control access to the network
     * (e.g., "captive portals" used to require agreement to Terms of Service before granting full
     * Internet access via a Wi-Fi hotspot).
     */
    case NETWORK_AUTHENTICATION_REQUIRED = "511";
}
