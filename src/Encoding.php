<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * How to encode one property of a multipart or application/x-www-form-urlencoded request body. These go in an
 * EncodingMap, which goes in the body's MediaType.
 * https://swagger.io/specification/v3/#encoding-object
 */
final readonly class Encoding implements \JsonSerializable
{
    private string $m_propertyName;
    private ?string $m_contentType;
    private HeaderCollection $m_headers;
    private ?Style $m_style;
    private ?bool $m_explode;
    private bool $m_allowReserved;
    private ExtensionMap $m_extensions;


    /**
     * @param string $propertyName - the name of the property in the body's schema that this is for.
     *
     * @param MimeType|string|null $contentType - the mime type to send the property as, e.g. MimeType::PNG for an
     * image in a multipart body.
     *
     * @param HeaderCollection $headers - extra headers to send with the property in a multipart body, e.g.
     * Content-Disposition. Content-Type can't be one of them, as it is set by $contentType.
     *
     * @param Style|null $style - how to serialize the property in an application/x-www-form-urlencoded body. Only the
     * styles that can be used in a query string can be used: form, spaceDelimited, pipeDelimited and deepObject.
     *
     * @param bool|null $explode - for an array or object value, whether to send it as separate values.
     *
     * @param bool $allowReserved - true if reserved characters (e.g. "/" and "?") are sent without being
     * percent-encoded.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the encoding.
     *
     * @throws InvalidValueException - if the property name is empty, the content type isn't a mime type, there is a
     * Content-Type header, or the style can't be used in a body.
     */
    public function __construct(
        string $propertyName,
        MimeType|string|null $contentType = null,
        HeaderCollection $headers = new HeaderCollection(),
        ?Style $style = null,
        ?bool $explode = null,
        bool $allowReserved = false,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($propertyName, "The encoding's property name");

        foreach ($headers->getKeys() as $headerName)
        {
            if (strtolower($headerName) === "content-type")
            {
                throw new InvalidValueException("An encoding's headers can't include Content-Type. Use \$contentType.");
            }
        }

        $allowedStyles = [Style::FORM, Style::SPACE_DELIMITED, Style::PIPE_DELIMITED, Style::DEEP_OBJECT];

        if ($style !== null && !in_array($style, $allowedStyles, true))
        {
            $message = "The {$style->value} style can't be used in an encoding, only form, spaceDelimited, "
                . "pipeDelimited and deepObject.";

            throw new InvalidValueException($message);
        }

        $this->m_propertyName = $propertyName;
        $this->m_contentType = ($contentType === null) ? null : Validator::normalizeMimeType($contentType);
        $this->m_headers = clone $headers;
        $this->m_style = $style;
        $this->m_explode = $explode;
        $this->m_allowReserved = $allowReserved;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'contentType' => $this->m_contentType,
            'headers' => $this->m_headers->isEmpty() ? null : $this->m_headers,
            'style' => $this->m_style?->value,
            'explode' => $this->m_explode,
            'allowReserved' => $this->m_allowReserved ? true : null,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getPropertyName() : string { return $this->m_propertyName; }
}
