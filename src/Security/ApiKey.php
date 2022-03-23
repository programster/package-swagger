<?php

/*
 * A security object for the swagger doc.
 * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#security-scheme-object
 */

namespace Programster\Swagger\Security;


use Programster\Swagger\ParameterLocation;

final class ApiKey implements InterfaceSecurityScheme, \JsonSerializable
{
    /**
     * @param string $name - The name of the header, query or cookie parameter to be used.
     * @param ParameterLocation $in - The location of the API key.
     */
    public function __construct(
        private readonly string $name,
        private readonly ParameterLocation $in
    )
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'apiKey',
            'name' => $this->name,
            'in' => $this->in
        ];
    }
}

