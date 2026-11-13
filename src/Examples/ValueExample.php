<?php

namespace Programster\Swagger\Examples;


/**
 * An example value, written out in full in the document.
 * https://swagger.io/specification/v3/#example-object
 */
final readonly class ValueExample implements \JsonSerializable
{
    private string|int|float|bool|array $m_value;


    /**
     * @param string|int|float|bool|array $value - the example, e.g. ['name' => "Bob"] for an object.
     */
    public function __construct(string|int|float|bool|array $value)
    {
        $this->m_value = $value;
    }


    public function jsonSerialize() : string|int|float|bool|array
    {
        return $this->m_value;
    }


    # Accessors
    public function getValue() : string|int|float|bool|array { return $this->m_value; }
}
