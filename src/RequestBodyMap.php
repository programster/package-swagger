<?php

/*
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;



class RequestBodyMap implements \JsonSerializable
{
    private array $m_responses;


    public function __construct(string $name, RequestBody | Reference $responseBody)
    {
        $this->m_responses = [$name => $responseBody];
    }


    public function add(string $name, RequestBody | Reference $responseBody)
    {
        if (isset($this->m_map[$name]))
        {
            throw new \Exception("{$name} already taken in " . __CLASS__);
        }

        $this->m_responses[$name] = $responseBody;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $name
     * @param RequestBody | Reference $responseBody
     * @return void
     */
    public function addOrReplace(string $name, RequestBody | Reference $responseBody)
    {
        $this->m_responses[$name] = $responseBody;
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
