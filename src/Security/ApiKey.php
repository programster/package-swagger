<?php

/*
 * A security object for the swagger doc.
 * https://swagger.io/specification/#security-scheme-object
 */

namespace Programster\Swagger\Security;


use Programster\Swagger\ParameterLocation;

final class ApiKey implements InterfaceSecurityScheme, \JsonSerializable
{
    private readonly string $m_name;
    private readonly ParameterLocation $m_in;


    /**
     * @param string $name - The name of the header, query or cookie parameter to be used.
     * @param ParameterLocation $in - The location of the API key.
     */
    public function __construct(string $name, ParameterLocation $in)
    {
        $this->m_name = $name;
        $this->m_in = $in;
    }


    public function jsonSerialize() : array
    {
        return [
            'type' => 'apiKey',
            'name' => $this->m_name,
            'in' => $this->m_in,
        ];
    }
}
