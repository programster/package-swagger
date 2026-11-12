<?php

namespace Programster\Swagger;


class HeaderMap implements \JsonSerializable
{
    private readonly array $m_map;


    /**
     * @param Header ...$headers - the headers, each of which must have a unique name.
     * @throws \Exception - if two of the headers have the same name.
     */
    public function __construct(Header ...$headers)
    {
        $map = [];

        foreach ($headers as $header)
        {
            if (isset($map[$header->getName()]))
            {
                throw new \Exception("Duplicate header: {$header->getName()}");
            }

            $map[$header->getName()] = $header;
        }

        $this->m_map = $map;
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
