<?php

/*
 * https://swagger.io/specification/#encoding-object
 */

namespace Programster\Swagger;


class Encoding implements \JsonSerializable
{
    private readonly MimeType $m_contentType;
    private readonly HeaderMap $m_headers;
    private readonly Style $m_style;
    private readonly ?bool $m_explode;
    private readonly bool $m_allowReserved;


    /**
     * https://swagger.io/specification/#encoding-object
     *
     * @param MimeType $contentType - the Content-Type to use for encoding this property.
     *
     * @param HeaderMap $headers - any extra headers to send with this property, e.g. Content-Disposition.
     *
     * @param Style|string $style - how the property's value will be serialized. A string must be one of the values
     * of the Style enum, e.g. "form".
     *
     * @param bool|null $explode - whether array or object values generate separate parameters. Null leaves it out.
     *
     * @param bool $allowReserved - whether the value may contain reserved characters without percent-encoding them.
     *
     * @throws \ValueError - if $style is a string that isn't one of the values of the Style enum.
     */
    public function __construct(
        MimeType $contentType,
        HeaderMap $headers,
        Style|string $style,
        ?bool $explode,
        bool $allowReserved
    )
    {
        $this->m_contentType = $contentType;
        $this->m_headers = $headers;
        $this->m_style = is_string($style) ? Style::from($style) : $style;
        $this->m_explode = $explode;
        $this->m_allowReserved = $allowReserved;
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [
            'contentType' => $this->m_contentType->value,
            'headers' => $this->m_headers,
            'style' => $this->m_style->value,
        ];

        if ($this->m_explode !== null)
        {
            $arrayForm['explode'] = $this->m_explode;
        }

        $arrayForm['allowReserved'] = $this->m_allowReserved;
        return $arrayForm;
    }
}
