<?php

/*
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;



class ResponseCollection implements \JsonSerializable
{
    private readonly array $m_arrayForm;


    public function __construct(MappedResponse ...$mappedResponses)
    {
        $arrayForm = [];

        foreach ($mappedResponses as $mappedResponse)
        {
            /* @var $mappedResponse \Programster\Swagger\MappedResponse */
            if (isset($arrayForm[$mappedResponse->getResponseCode()->value]))
            {
                throw new \Exception("Overlapping response code: " . $mappedResponse->getResponseCode());
            }

            $arrayForm[$mappedResponse->getResponseCode()->value] = $mappedResponse->getResponse();
        }

        $this->m_arrayForm = $arrayForm;
    }


    public function toArray() : array
    {
        return $this->m_arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
