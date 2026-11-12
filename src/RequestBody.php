<?php

/*
 * https://swagger.io/specification/#request-body-object
 */

namespace Programster\Swagger;


class RequestBody implements \JsonSerializable
{
    private readonly ContentMap $m_content;
    private readonly ?string $m_description;
    private readonly ?bool $m_required;


    public function __construct(ContentMap $content, ?string $description = null, ?bool $required = null)
    {
        $this->m_content = $content;
        $this->m_description = $description;
        $this->m_required = $required;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'required' => $this->m_required,
            'description' => $this->m_description,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['content' => $this->m_content], array_filter($optionalFields, $isProvided));
    }
}
