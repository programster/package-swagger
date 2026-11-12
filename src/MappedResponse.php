<?php

namespace Programster\Swagger;

class MappedResponse
{
    private readonly ResponseCode $m_code;
    private readonly Response|Reference $m_response;


    public function __construct(ResponseCode $code, Response|Reference $response)
    {
        $this->m_code = $code;
        $this->m_response = $response;
    }


    # Accessors
    public function getResponseCode() : ResponseCode { return $this->m_code; }
    public function getResponse() : Response|Reference { return $this->m_response; }
}
