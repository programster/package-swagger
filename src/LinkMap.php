<?php

namespace Programster\Swagger;


class LinkMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $key, Link | Reference $linkOrReference)
    {
        $this->m_map = [
            $key => $linkOrReference
        ];
    }


    public function add(string $key, Link | Reference $linkOrReference)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_map[$key] = $linkOrReference;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param Link|Reference $linkOrReference
     * @return void
     */
    public function addOrReplace(string $key, Link | Reference $linkOrReference)
    {
        $this->m_map[$key] = $linkOrReference;
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
