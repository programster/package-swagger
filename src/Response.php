<?php

/*
 * https://swagger.io/specification/#response-object
 */

namespace Programster\Swagger;

class Response implements \JsonSerializable
{
    private function __construct(
        private readonly string $description,
        private readonly ContentMap $contentMap,
        private readonly ?HeaderMap $headerMap = null,
        private readonly ?LinkMap $linkMap = null
    )
    {

    }


    public function toArray() : array
    {
        $arrayForm = array(
            'description' => $this->m_description,
            'content' => $this->contentMap
        );

        if ($this->headerMap !== null) { $arrayForm['headers'] = $this->headerMap; }
        if ($this->linkMap !== null) { $arrayForm['links'] = $this->linkMap; }

        return $arrayForm;
    }


    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
