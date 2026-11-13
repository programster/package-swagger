<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A link to documentation elsewhere, for the document, a tag or an operation.
 * https://swagger.io/specification/v3/#external-documentation-object
 */
final readonly class ExternalDocumentation implements \JsonSerializable
{
    private string $m_url;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string $url - the URL of the documentation.
     * @param string|null $description - a description of the documentation. CommonMark syntax may be used.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the external documentation.
     * @throws InvalidValueException - if the URL isn't a URL.
     */
    public function __construct(string $url, ?string $description = null, ExtensionMap $extensions = new ExtensionMap())
    {
        Validator::assertUrl($url, "The external documentation's URL");
        $this->m_url = $url;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'description' => $this->m_description,
            'url' => $this->m_url,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
