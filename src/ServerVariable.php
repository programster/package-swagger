<?php

/*
 * https://swagger.io/specification/#server-variable-object
 */

namespace Programster\Swagger;


class ServerVariable implements \JsonSerializable
{
    private readonly string $m_default;
    private readonly ?string $m_description;
    private readonly ?array $m_enum;


    /**
     * @param string $default - The default value to use for substitution, which SHALL be sent if an alternate value is
     * not supplied. Note this behavior is different than the Schema Object's treatment of default values, because in
     * those cases parameter values are optional. If the enum is defined, the value SHOULD exist in the enum's values.
     *
     * @param string|null $description - An optional description for the server variable. CommonMark syntax MAY be used
     * for rich text representation.
     *
     * @param string[]|null $enum - An enumeration of string values to be used if the substitution options are from a
     * limited set. The array SHOULD NOT be empty if provided.
     *
     * @throws \Exception - if $enum is empty, or any of its values aren't strings.
     */
    public function __construct(string $default, ?string $description = null, ?array $enum = null)
    {
        if ($enum !== null && count($enum) === 0)
        {
            $message = "Enum cannot be an empty array when creating a ServerVariable. Did you mean to use 'null'?";
            throw new \Exception($message);
        }

        if ($enum !== null)
        {
            foreach ($enum as $value)
            {
                if (is_string($value) === false)
                {
                    throw new \Exception("Values in enum array need to all be strings.");
                }
            }
        }

        $this->m_default = $default;
        $this->m_description = $description;
        $this->m_enum = $enum;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'enum' => $this->m_enum,
            'description' => $this->m_description,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['default' => $this->m_default], array_filter($optionalFields, $isProvided));
    }
}
