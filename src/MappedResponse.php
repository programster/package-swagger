<?php

namespace Programster\Swagger;

class MappedResponse
{
    public function __construct(
        private readonly ResponseCode $code,
        private readonly Response | Reference $response
    )
    {

    }


    # Accessors
    public function getResponseCode() : ResponseCode { return $this->code; }
    public function getResponse() : Response | Reference { return $this->response; }
}
