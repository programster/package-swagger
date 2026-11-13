<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A tag, for grouping operations, e.g. in Swagger UI. Operations are tagged by the tag's name, and only need it to
 * be described here if it should have a description or external documentation.
 * https://swagger.io/specification/v3/#tag-object
 */
final readonly class Tag implements \JsonSerializable
{
    private string $m_name;
    private ?string $m_description;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * @param string $name - the name of the tag, e.g. "users".
     * @param string|null $description - a description of the tag. CommonMark syntax may be used.
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the tag.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the tag.
     * @throws InvalidValueException - if the name is empty.
     */
    public function __construct(
        string $name,
        ?string $description = null,
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($name, "The tag's name");
        $this->m_name = $name;
        $this->m_description = $description;
        $this->m_externalDocs = $externalDocs;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'name' => $this->m_name,
            'description' => $this->m_description,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
}
