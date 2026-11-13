<?php

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\Schemas\SchemaInterface;
use Programster\Swagger\Schemas\SchemaObject;


/**
 * What content of one mime type looks like, e.g. the JSON that a response returns. These go in a ContentMap.
 * https://swagger.io/specification/v3/#media-type-object
 */
final readonly class MediaType implements \JsonSerializable
{
    private string $m_mimeType;
    private ?SchemaInterface $m_schema;
    private ValueExample|ExampleMap|null $m_example;
    private EncodingMap $m_encoding;
    private ExtensionMap $m_extensions;


    /**
     * @param MimeType|string $mimeType - the mime type, e.g. MimeType::JSON, or a string such as "application/xml"
     * for one that the MimeType enum doesn't have.
     *
     * @param SchemaInterface|null $schema - the schema of the content, or a reference to one.
     *
     * @param ValueExample|ExampleMap|null $example - an example of the content, or a map of named examples.
     *
     * @param EncodingMap $encoding - how to encode the content's properties. This can only be used for multipart and
     * application/x-www-form-urlencoded content, and only for properties that the schema has.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the media type.
     *
     * @throws InvalidValueException - if the mime type isn't a mime type, or there is an encoding that can't be used.
     */
    public function __construct(
        MimeType|string $mimeType,
        ?SchemaInterface $schema = null,
        ValueExample|ExampleMap|null $example = null,
        EncodingMap $encoding = new EncodingMap(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $normalizedMimeType = Validator::normalizeMimeType($mimeType);

        if (!$encoding->isEmpty())
        {
            $baseType = strtolower(trim(explode(";", $normalizedMimeType)[0]));

            $canBeEncoded = (
                   str_starts_with($baseType, "multipart/")
                || $baseType === MimeType::FORM_URL_ENCODED->value
            );

            if (!$canBeEncoded)
            {
                $message = "An encoding can only be given for multipart or application/x-www-form-urlencoded content, "
                    . "not {$normalizedMimeType}.";

                throw new InvalidValueException($message);
            }

            if ($schema instanceof SchemaObject)
            {
                $propertyNames = $schema->getProperties()->getKeys();

                foreach ($encoding->getKeys() as $propertyName)
                {
                    if (!in_array($propertyName, $propertyNames, true))
                    {
                        $message = "There is an encoding for the \"{$propertyName}\" property, but the schema doesn't "
                            . "have a property with that name.";

                        throw new InvalidValueException($message);
                    }
                }
            }
        }

        $this->m_mimeType = $normalizedMimeType;
        $this->m_schema = $schema;
        $this->m_example = ($example instanceof ExampleMap) ? clone $example : $example;
        $this->m_encoding = clone $encoding;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $isExampleMap = $this->m_example instanceof ExampleMap;

        $fields = [
            'schema' => $this->m_schema,
            'example' => $isExampleMap ? null : $this->m_example,
            'examples' => $isExampleMap ? $this->m_example : null,
            'encoding' => $this->m_encoding->isEmpty() ? null : $this->m_encoding,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getMimeType() : string { return $this->m_mimeType; }
}
