<?php

/*
 * https://swagger.io/specification/#media-type-object
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\ArrayShape;
use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\ValueExample;
use Programster\Swagger\Schemas\SchemaInterface;


class MediaType implements \JsonSerializable
{
    private readonly SchemaInterface|Reference $m_schema;
    private readonly ExampleMap|ValueExample|ExternalValueExample|null $m_example;
    private readonly ?Encoding $m_encoding;


    /**
     * https://swagger.io/specification/#media-type-object
     * @param SchemaInterface|Reference $schema - the schema for the content, or a reference to one.
     * @param ExampleMap|ValueExample|ExternalValueExample|null $example - an example, or a map of named examples.
     * @param Encoding|null $encoding - how to encode the content.
     */
    public function __construct(
        SchemaInterface|Reference $schema,
        ExampleMap|ValueExample|ExternalValueExample|null $example = null,
        ?Encoding $encoding = null
    )
    {
        $this->m_schema = $schema;
        $this->m_example = $example;
        $this->m_encoding = $encoding;
    }


    #[ArrayShape(['schema' => "mixed", 'encoding' => "mixed", 'examples' => "mixed", 'example' => "mixed"])]
    public function toArray() : array
    {
        $exampleKey = ($this->m_example instanceof ExampleMap) ? 'examples' : 'example';

        $optionalFields = [
            $exampleKey => $this->m_example,
            'encoding' => $this->m_encoding,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['schema' => $this->m_schema], array_filter($optionalFields, $isProvided));
    }


    #[ArrayShape(['schema' => "mixed", 'encoding' => "mixed", 'examples' => "mixed", 'example' => "mixed"])]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
