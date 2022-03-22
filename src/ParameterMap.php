<?php

/*
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;



class ParameterMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $name, Parameter | Reference $parameter)
    {
        $this->m_map = [$name => $parameter];
    }


    public function add(string $key, Parameter | Reference $parameter)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_map[$key] = $parameter;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param Response | Reference $parameter
     * @return void
     */
    public function addOrReplace(string $key, Response | Parameter $parameter)
    {
        $this->m_map[$key] = $parameter;
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
