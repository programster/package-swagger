<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;


/**
 * A response for a response code, to put in an operation's ResponseCollection.
 */
final readonly class MappedResponse
{
    private string $m_code;
    private ResponseInterface $m_response;


    /**
     * @param ResponseCode|int $code - the response code, e.g. ResponseCode::OK, ResponseCode::ANY_CLIENT_ERROR for
     * any 4XX code, or ResponseCode::DEFAULT for any code that the operation doesn't have another response for. A
     * code that ResponseCode doesn't have can be given as an int, e.g. 499.
     *
     * @param ResponseInterface $response - the response, or a reference to one.
     *
     * @throws InvalidValueException - if the code is an int that isn't an HTTP status code (from 100 to 599).
     */
    public function __construct(ResponseCode|int $code, ResponseInterface $response)
    {
        if (is_int($code) && ($code < 100 || $code > 599))
        {
            throw new InvalidValueException("{$code} isn't an HTTP status code, which must be from 100 to 599.");
        }

        $this->m_code = ($code instanceof ResponseCode) ? $code->value : (string)$code;
        $this->m_response = $response;
    }


    # Accessors
    public function getCode() : string { return $this->m_code; }
    public function getResponse() : ResponseInterface { return $this->m_response; }
}
