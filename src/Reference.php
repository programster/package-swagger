<?php

namespace Programster\Swagger;


class Reference implements \JsonSerializable
{
    public function __construct(private readonly string $reference)
    {

    }


    /**
     * Create a reference to a schema (object definition) within this document.
     * @param string $objectName - the name of the object within this document. E.g. "Pet"
     * @return Reference
     */
    public static function createReferenceObject(string $objectName)
    {
        return new Reference("#/components/schemas/{$objectName}");
    }


    /**
     * Create a reference to another document.
     * @param string $documentFilepath - the name of the document. E.g. "Pet.json"
     * @return Reference
     */
    public static function createRelativeSchemaDocument(string $documentFilepath)
    {
        return new Reference("{$documentFilepath}");
    }


    /**
     * Create a reference to a schema embedded in another relative document.
     * @param string $documentName - the name of the document. E.g. "definitions.json"
     * @param string $objectName - the name of the object. E.g. "Pet"
     * @return Reference - the generated reference object.
     */
    public static function createRelativeDocumentEmbeddedSchema(string $documentName, string $objectName)
    {
        return new Reference("{$documentName}#/{$objectName}");
    }


    public function toArray() : array
    {
        return [
            '$ref' => $this->reference,
        ];
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
