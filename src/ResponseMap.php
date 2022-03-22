<?php

/*
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;



class ResponseMap implements \JsonSerializable
{
    private array $m_responses;


    public function __construct(ResponseCode $httpStatusCode, Response | Reference $responseOrReference)
    {
        $this->m_responses = [$httpStatusCode->value => $responseOrReference];
    }


    public function add(string $key, Response | Reference $responseOrReference)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_responses[$key] = $responseOrReference;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param Response | Reference $responseOrReference
     * @return void
     */
    public function addOrReplace(string $key, Response | Reference $responseOrReference)
    {
        $this->m_responses[$key] = $responseOrReference;
    }


    public function toArray() : array
    {
        return $this->m_responses;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
