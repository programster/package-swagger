<?php

namespace Programster\Swagger\Extensions;

use Programster\Swagger\Exceptions\InvalidValueException;


/**
 * A specification extension: an extra field, whose name starts with "x-", that tools can use for things OpenAPI
 * doesn't cover, e.g. Redoc's "x-logo". These go in an ExtensionMap, which most objects take as their last parameter.
 * https://swagger.io/specification/v3/#specification-extensions
 */
final readonly class Extension
{
    private string $m_name;
    private string|int|float|bool|array|\stdClass|\JsonSerializable $m_value;


    /**
     * @param string $name - the name of the field, which must start with "x-", e.g. "x-logo". Names that start with
     * "x-oai-" or "x-oas-" are reserved for OpenAPI itself, so can't be used.
     *
     * @param string|int|float|bool|array|\stdClass|\JsonSerializable $value - the value of the field, which can be
     * anything that can be output as JSON, e.g. ['url' => "https://example.com/logo.png"].
     *
     * @throws InvalidValueException - if the name doesn't start with "x-", or is reserved.
     */
    public function __construct(string $name, string|int|float|bool|array|\stdClass|\JsonSerializable $value)
    {
        if (!str_starts_with($name, "x-") || strlen($name) === 2)
        {
            $message = "An extension's name must start with \"x-\" and have something after it, but was \"{$name}\".";
            throw new InvalidValueException($message);
        }

        $lowerName = strtolower($name);

        if (str_starts_with($lowerName, "x-oai-") || str_starts_with($lowerName, "x-oas-"))
        {
            $message = "The extension name \"{$name}\" can't be used, as names that start with \"x-oai-\" or "
                . "\"x-oas-\" are reserved for OpenAPI itself.";

            throw new InvalidValueException($message);
        }

        $this->m_name = $name;
        $this->m_value = $value;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getValue() : string|int|float|bool|array|\stdClass|\JsonSerializable { return $this->m_value; }
}
