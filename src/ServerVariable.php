<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A variable in a server's URL, e.g. the "environment" in "https://{environment}.example.com".
 * https://swagger.io/specification/v3/#server-variable-object
 */
final readonly class ServerVariable implements \JsonSerializable
{
    private string $m_name;
    private string $m_default;
    private ?string $m_description;
    private ?array $m_enum;
    private ExtensionMap $m_extensions;


    /**
     * @param string $name - the name of the variable, as it is in the server's URL (without the braces).
     *
     * @param string $default - the value to use when no other is given.
     *
     * @param string|null $description - a description of the variable. CommonMark syntax may be used.
     *
     * @param string[]|null $enum - the only values that the variable can have, if it is limited to some. This can't
     * be empty, and must include the default.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the variable.
     *
     * @throws InvalidValueException - if the name is empty, or the enum is empty, has a value that isn't a string,
     * or doesn't include the default.
     */
    public function __construct(
        string $name,
        string $default,
        ?string $description = null,
        ?array $enum = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($name, "The server variable's name");

        if ($enum !== null)
        {
            if (count($enum) === 0)
            {
                $message = "The \"{$name}\" server variable's enum can't be empty. Leave it as null if any value is "
                    . "allowed.";

                throw new InvalidValueException($message);
            }

            foreach ($enum as $value)
            {
                if (!is_string($value))
                {
                    $type = get_debug_type($value);
                    $message = "The \"{$name}\" server variable's enum has a {$type}, not a string.";
                    throw new InvalidValueException($message);
                }
            }

            if (!in_array($default, $enum, true))
            {
                $message = "The \"{$name}\" server variable's default (\"{$default}\") isn't one of its enum's "
                    . "values.";

                throw new InvalidValueException($message);
            }
        }

        $this->m_name = $name;
        $this->m_default = $default;
        $this->m_description = $description;
        $this->m_enum = ($enum === null) ? null : array_values($enum);
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'enum' => $this->m_enum,
            'default' => $this->m_default,
            'description' => $this->m_description,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
}
