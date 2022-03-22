<?php

/*
 * https://swagger.io/specification/#media-type-object
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class MediaType implements \JsonSerializable
{
    public function __construct(
        private readonly Schema | Reference $schema,
        private readonly Examples | Example | null $example,
        private readonly ?EncodingObject $encoding
    )
    {

    }

    public function toArray() : array
    {
        $arrayForm = [
            'schema' => $this->schema,
        ];

        if ($this->example !== null)
        {
            if (get_class($this->example) === Example::class)
            {
                $arrayForm['example'] = $this->example;
            }
            else
            {
                $arrayForm['examples'] = $this->example;
            }
        }

        if ($this->encoding !== null)
        {
            $arrayForm['encoding'] = $this->encoding;
        }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
