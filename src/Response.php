<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A response that an operation can give.
 * https://swagger.io/specification/v3/#response-object
 */
final readonly class Response implements ResponseInterface
{
    private string $m_description;
    private ?ContentMap $m_content;
    private HeaderCollection $m_headers;
    private LinkMap $m_links;
    private ExtensionMap $m_extensions;


    /**
     * @param string $description - a description of the response. OpenAPI requires this. CommonMark syntax may be
     * used.
     *
     * @param ContentMap|null $content - the mime types the response's body can be, and what it looks like in each.
     * Leave it out for a response without a body, e.g. a 204.
     *
     * @param HeaderCollection $headers - the headers the response has, under their names. Content-Type can't be one
     * of them, as it is set by the content.
     *
     * @param LinkMap $links - links to other operations that can be called with values from this response.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the response.
     *
     * @throws InvalidValueException - if the description is empty, or there's a Content-Type header.
     */
    public function __construct(
        string $description,
        ?ContentMap $content = null,
        HeaderCollection $headers = new HeaderCollection(),
        LinkMap $links = new LinkMap(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($description, "The response's description");

        foreach ($headers->getKeys() as $headerName)
        {
            if (strtolower($headerName) === "content-type")
            {
                $message = "A response's headers can't include Content-Type, as it is set by the content.";
                throw new InvalidValueException($message);
            }
        }

        $this->m_description = $description;
        $this->m_content = ($content === null) ? null : clone $content;
        $this->m_headers = clone $headers;
        $this->m_links = clone $links;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'description' => $this->m_description,
            'headers' => $this->m_headers->isEmpty() ? null : $this->m_headers,
            'content' => $this->m_content,
            'links' => $this->m_links->isEmpty() ? null : $this->m_links,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
