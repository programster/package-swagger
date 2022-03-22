<?php

namespace Programster\Swagger;


class SecuritySchemeMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $key, ApiKey | HttpBasicAuth | ImplicitOauth2 | JwtBearer | Reference $scheme)
    {
        $this->m_map = [
            $key => $scheme
        ];
    }


    public function add(string $key, ApiKey | HttpBasicAuth | ImplicitOauth2 | JwtBearer | Reference $scheme)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_map[$key] = $scheme;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param SecurityScheme|Reference $scheme
     * @return void
     */
    public function addOrReplace(string $key, ApiKey | HttpBasicAuth | ImplicitOauth2 | JwtBearer | Reference $scheme)
    {
        $this->m_map[$key] = $scheme;
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
