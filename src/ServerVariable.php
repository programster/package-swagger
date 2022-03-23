<?php

/*
 * https://swagger.io/specification/#encoding-object
 */

namespace Programster\Swagger;


class ServerVariable implements \JsonSerializable
{
    /**
     * @param string $default - The default value to use for substitution, which SHALL be sent if an alternate value is
     * not supplied. Note this behavior is different than the Schema Object's treatment of default values, because in
     * those cases parameter values are optional. If the enum is defined, the value SHOULD exist in the enum's values.
     *
     * @param string|null $description - An optional description for the server variable. CommonMark syntax MAY be used
     * for rich text representation.
     *
     * @param array|null $enum - An enumeration of string values to be used if the substitution options are from a
     * limited set. The array SHOULD NOT be empty if provided.
     */
    public function __construct(
        private string $default,
        private ?string $description = null,
        private ?array $enum = null,
    )
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
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('default' => $this->default);

        if ($this->enum !== null) { $arrayForm['enum'] = $this->enum; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }

        return $arrayForm;
    }
}
