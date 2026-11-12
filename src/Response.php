<?php

/*
 * https://swagger.io/specification/#response-object
 */

namespace Programster\Swagger;

class Response implements \JsonSerializable
{
    private readonly string $m_description;
    private readonly ContentMap $m_contentMap;
    private readonly ?HeaderMap $m_headerMap;
    private readonly ?LinkMap $m_linkMap;


    public function __construct(
        string $description,
        ContentMap $contentMap,
        ?HeaderMap $headerMap = null,
        ?LinkMap $linkMap = null
    )
    {
        $this->m_description = $description;
        $this->m_contentMap = $contentMap;
        $this->m_headerMap = $headerMap;
        $this->m_linkMap = $linkMap;
    }


    public function toArray() : array
    {
        $requiredFields = [
            'description' => $this->m_description,
            'content' => $this->m_contentMap,
        ];

        $optionalFields = [
            'headers' => $this->m_headerMap,
            'links' => $this->m_linkMap,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge($requiredFields, array_filter($optionalFields, $isProvided));
    }


    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
