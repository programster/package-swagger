<?php

/*
 * https://swagger.io/specification/#header-object
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Header implements \JsonSerializable
{
    /**
     * Create a header.
     * @param string $description - the description for this header.
     * @param Type $type - the type. E.g. integer/string
     */
    public function __construct(
        private readonly string $description,
        private readonly Type $type
    )
    {

    }


    public function toArray() : array
    {
        return [
            'description' => $this->description,
            'schema' => [
                'type' => $this->type,
            ]
        ];
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
