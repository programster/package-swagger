<?php

namespace Programster\Swagger\References;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * A reference ($ref) to an object that is defined elsewhere: in this document's components, or in another document.
 * Each kind of object has its own kind of reference (e.g. SchemaReference), so a reference can only be used where
 * that kind of object can. Most kinds can be in the components, so their references extend ComponentReference.
 */
abstract readonly class Reference implements \JsonSerializable
{
    private string $m_reference;


    final protected function __construct(string $reference)
    {
        $this->m_reference = $reference;
    }


    /**
     * Create a reference to the whole of another document, e.g. "Pet.json", or "https://example.com/Pet.json".
     *
     * @param string $uri - the relative path or URL of the document.
     *
     * @return static
     *
     * @throws InvalidValueException - if $uri is empty, or contains a "#", which createToDocumentPath() is for.
     */
    public static function createToDocument(string $uri) : static
    {
        Validator::assertNotEmpty($uri, "The document's URI");

        if (str_contains($uri, "#"))
        {
            throw new InvalidValueException("The document's URI can't contain a \"#\". Use createToDocumentPath().");
        }

        return new static($uri);
    }


    /**
     * Create a reference to something inside another document, e.g. ("definitions.json", "/Pet") for
     * "definitions.json#/Pet".
     *
     * @param string $uri - the relative path or URL of the document.
     *
     * @param string $jsonPointer - the JSON pointer to the thing within the document, which starts with a "/".
     *
     * @return static
     *
     * @throws InvalidValueException - if $uri is empty or contains a "#", or $jsonPointer doesn't start with "/".
     */
    public static function createToDocumentPath(string $uri, string $jsonPointer) : static
    {
        $document = static::createToDocument($uri);

        if (str_starts_with($jsonPointer, "/") === false)
        {
            throw new InvalidValueException("The JSON pointer must start with \"/\", but was \"{$jsonPointer}\".");
        }

        return new static("{$document->getReference()}#{$jsonPointer}");
    }


    public function jsonSerialize() : \stdClass
    {
        return (object)['$ref' => $this->m_reference];
    }


    # Accessors
    public function getReference() : string { return $this->m_reference; }
}
