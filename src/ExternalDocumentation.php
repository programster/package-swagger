<?php

namespace Programster\Swagger;


class ExternalDocumentation implements \JsonSerializable
{
    private readonly string $m_url;
    private readonly ?string $m_description;


    public function __construct(string $url, ?string $description = null)
    {
        $this->m_url = $url;
        $this->m_description = $description;
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = ['url' => $this->m_url];

        if ($this->m_description !== null)
        {
            $arrayForm['description'] = $this->m_description;
        }

        return $arrayForm;
    }
}
