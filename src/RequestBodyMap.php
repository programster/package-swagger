<?php

/*
 * https://swagger.io/specification/#components-object
 */

namespace Programster\Swagger;


class RequestBodyMap implements \JsonSerializable
{
    private array $m_map;


    /**
     * @param string $name - the unique name for the request body.
     * @param RequestBody|Reference $responseBody - the request body, or a reference to one.
     */
    public function __construct(string $name, RequestBody|Reference $responseBody)
    {
        $this->m_map = [$name => $responseBody];
    }


    /**
     * Add another request body.
     * @param string $name - the unique name for the request body.
     * @param RequestBody|Reference $responseBody - the request body, or a reference to one.
     * @return void
     * @throws \Exception - if there's already a request body with that name.
     */
    public function add(string $name, RequestBody|Reference $responseBody)
    {
        if (isset($this->m_map[$name]))
        {
            throw new \Exception("{$name} already taken in " . __CLASS__);
        }

        $this->m_map[$name] = $responseBody;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $name - the unique name for the request body.
     * @param RequestBody|Reference $responseBody - the request body, or a reference to one.
     * @return void
     */
    public function addOrReplace(string $name, RequestBody|Reference $responseBody)
    {
        $this->m_map[$name] = $responseBody;
    }


    public function toArray() : array
    {
        return $this->m_map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
