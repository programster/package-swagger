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
    /**
     * https://swagger.io/specification/#media-type-object
     * @param SchemaString|SchemaNumber|SchemaArray|SchemaInt|SchemaObject|Reference $schema
     * @param ExampleMap|ValueExample|ExternalValueExample |null $example
     * @param Encoding|null $encoding
     */
    public function __construct(
        private readonly SchemaInterface | Reference $schema,
        private readonly ExampleMap | ValueExample | ExternalValueExample | null $example = null,
        private readonly ?Encoding $encoding = null
    )
    {

    }


    #[ArrayShape(['schema' => "mixed", 'encoding' => "mixed", 'examples' => "mixed", 'example' => "mixed"])]
    public function toArray() : array
    {
        $arrayForm = [
            'schema' => $this->schema,
        ];

        if ($this->example !== null)
        {
            if (get_class($this->example) === ExampleMap::class)
            {
                $arrayForm['examples'] = $this->example;
            }
            else
            {
                $arrayForm['example'] = $this->example;
            }
        }

        if ($this->encoding !== null)
        {
            $arrayForm['encoding'] = $this->encoding;
        }

        return $arrayForm;
    }


    #[ArrayShape(['schema' => "mixed", 'encoding' => "mixed", 'examples' => "mixed", 'example' => "mixed"])]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
