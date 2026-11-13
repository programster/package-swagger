<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * The body of a request.
 * https://swagger.io/specification/v3/#request-body-object
 */
final readonly class RequestBody implements RequestBodyInterface
{
    private ContentMap $m_content;
    private ?string $m_description;
    private bool $m_required;
    private ExtensionMap $m_extensions;


    /**
     * @param ContentMap $content - the mime types the body can be sent as, and what it looks like in each.
     * @param string|null $description - a description of the body. CommonMark syntax may be used.
     * @param bool $required - true if the body must be sent.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the request body.
     * @throws InvalidValueException - if the content is empty.
     */
    public function __construct(
        ContentMap $content,
        ?string $description = null,
        bool $required = false,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if ($content->isEmpty())
        {
            throw new InvalidValueException("A request body's content can't be empty.");
        }

        $this->m_content = clone $content;
        $this->m_description = $description;
        $this->m_required = $required;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'description' => $this->m_description,
            'content' => $this->m_content,
            'required' => $this->m_required ? true : null,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
