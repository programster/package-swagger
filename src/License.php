<?php

namespace Programster\Swagger;


class License implements \JsonSerializable
{
    private readonly string $m_name;
    private readonly ?string $m_url;


    public function __construct(string $name, ?string $url)
    {
        $this->m_name = $name;
        $this->m_url = $url;
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = ['name' => $this->m_name];

        if ($this->m_url !== null)
        {
            $arrayForm['url'] = $this->m_url;
        }

        return $arrayForm;
    }
}
