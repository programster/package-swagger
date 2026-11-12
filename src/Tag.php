<?php

namespace Programster\Swagger;


class Tag implements \JsonSerializable
{
    private readonly string $m_name;
    private readonly ?string $m_description;
    private readonly ?ExternalDocumentation $m_externalDocs;


    public function __construct(string $name, ?string $description = null, ?ExternalDocumentation $externalDocs = null)
    {
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_externalDocs = $externalDocs;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'externalDocs' => $this->m_externalDocs,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['name' => $this->m_name], array_filter($optionalFields, $isProvided));
    }
}
