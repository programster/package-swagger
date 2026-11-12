<?php

namespace Programster\Swagger;


class Server implements \JsonSerializable
{
    private readonly string $m_url;
    private readonly ?string $m_description;
    private readonly ?ServerVariableMap $m_variables;


    public function __construct(string $url, ?string $description, ?ServerVariableMap $variables)
    {
        $this->m_url = $url;
        $this->m_description = $description;
        $this->m_variables = $variables;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'variables' => $this->m_variables,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['url' => $this->m_url], array_filter($optionalFields, $isProvided));
    }
}
