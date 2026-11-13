<?php

namespace Programster\Swagger\Internal;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\MimeType;
use Programster\Swagger\References\SchemaReference;
use Programster\Swagger\Schemas\Discriminator;
use Programster\Swagger\Schemas\SchemaCollection;
use Programster\Swagger\Schemas\Type;
use Programster\Swagger\Schemas\Xml;


/**
 * Checks the values that objects are created with, so that each constructor can reject a bad value straight away.
 *
 * @internal - this is used by the package itself, and isn't part of its public API.
 */
final class Validator
{
    /**
     * @throws InvalidValueException - if $url isn't a URL.
     */
    public static function assertUrl(string $url, string $fieldName) : void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false)
        {
            throw new InvalidValueException("{$fieldName} must be a URL, but was \"{$url}\".");
        }
    }


    /**
     * @throws InvalidValueException - if $email isn't an email address.
     */
    public static function assertEmail(string $email, string $fieldName) : void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
        {
            throw new InvalidValueException("{$fieldName} must be an email address, but was \"{$email}\".");
        }
    }


    /**
     * @throws InvalidValueException - if $value is an empty string.
     */
    public static function assertNotEmpty(string $value, string $fieldName) : void
    {
        if ($value === "")
        {
            throw new InvalidValueException("{$fieldName} can't be an empty string.");
        }
    }


    /**
     * Check a name that something is given in the components, which OpenAPI only allows to use letters, numbers,
     * dots, dashes and underscores.
     *
     * @throws InvalidValueException - if $name isn't a valid name for a component.
     */
    public static function assertComponentName(string $name, string $fieldName) : void
    {
        if (preg_match('/^[a-zA-Z0-9.\-_]+$/', $name) !== 1)
        {
            $message = "{$fieldName} \"{$name}\" can only contain letters, numbers, dots, dashes and underscores, as "
                . "it is used as the name of a component.";

            throw new InvalidValueException($message);
        }
    }


    /**
     * Check an HTTP header's name, which can't be empty or contain spaces, colons and the like.
     *
     * @throws InvalidValueException - if $name isn't a valid header name.
     */
    public static function assertHeaderName(string $name) : void
    {
        if (preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/', $name) !== 1)
        {
            throw new InvalidValueException("\"{$name}\" isn't a valid HTTP header name.");
        }
    }


    /**
     * @throws InvalidValueException - if $value is negative.
     */
    public static function assertNotNegative(?int $value, string $fieldName) : void
    {
        if ($value !== null && $value < 0)
        {
            throw new InvalidValueException("{$fieldName} can't be negative, but was {$value}.");
        }
    }


    /**
     * @throws InvalidValueException - if both values are given, and $minimum is greater than $maximum.
     */
    public static function assertNotGreater(
        int|float|null $minimum,
        int|float|null $maximum,
        string $minimumName,
        string $maximumName
    ) : void
    {
        if ($minimum !== null && $maximum !== null && $minimum > $maximum)
        {
            $message = "{$minimumName} ({$minimum}) can't be greater than {$maximumName} ({$maximum}).";
            throw new InvalidValueException($message);
        }
    }


    /**
     * Get a mime type (also known as a media type) as a string, checking it if it was given as a string.
     *
     * @param MimeType|string $mimeType - the mime type, e.g. MimeType::JSON, or a string such as "application/xml"
     * for one that the MimeType enum doesn't have. A string may have parameters, e.g. "text/plain; charset=utf-8".
     *
     * @return string
     *
     * @throws InvalidValueException - if $mimeType is a string that isn't a mime type.
     */
    public static function normalizeMimeType(MimeType|string $mimeType) : string
    {
        if ($mimeType instanceof MimeType)
        {
            $result = $mimeType->value;
        }
        else
        {
            $token = '[a-z0-9!#$&^_.+*-]+';
            $pattern = "~^{$token}/{$token}(\s*;\s*{$token}=(\"[^\"]*\"|{$token}))*$~i";

            if (preg_match($pattern, $mimeType) !== 1)
            {
                $message = "\"{$mimeType}\" isn't a mime type. It should look like \"type/subtype\", e.g. "
                    . "\"application/xml\".";

                throw new InvalidValueException($message);
            }

            $result = $mimeType;
        }

        return $result;
    }

    /**
     * Check the values that a schema's enum limits it to, and that its default and example are among them.
     *
     * @param array|null $enum - the values, or null if the schema isn't limited to some.
     *
     * @param \Closure $isOfType - checks that a value is of the schema's type, returning a bool.
     *
     * @param string $typeName - the name of the schema's type, for the message, e.g. "string".
     *
     * @param string|int|float|null $default - the schema's default, if it has one.
     *
     * @param string|int|float|null $example - the schema's example, if it has one.
     *
     * @return void
     *
     * @throws InvalidValueException - if the enum is empty, has a value of the wrong type or the same value twice,
     * or doesn't have the default or example.
     */
    public static function assertEnum(
        ?array $enum,
        \Closure $isOfType,
        string $typeName,
        string|int|float|null $default,
        string|int|float|null $example
    ) : void
    {
        if ($enum !== null)
        {
            if (count($enum) === 0)
            {
                throw new InvalidValueException("An enum can't be empty. Leave it as null if any value is allowed.");
            }

            $seen = [];

            foreach ($enum as $value)
            {
                if (!$isOfType($value))
                {
                    $type = get_debug_type($value);
                    $message = "The enum can only have {$typeName} values, but has a {$type} in it.";
                    throw new InvalidValueException($message);
                }

                if (in_array($value, $seen, true))
                {
                    throw new InvalidValueException("The enum has " . json_encode($value) . " in it twice.");
                }

                $seen[] = $value;
            }

            $namedValues = ['default' => $default, 'example' => $example];

            foreach ($namedValues as $name => $value)
            {
                if ($value !== null && !in_array($value, $enum, true))
                {
                    $message = "The schema's {$name} (" . json_encode($value) . ") isn't one of its enum's values.";
                    throw new InvalidValueException($message);
                }
            }
        }
    }


    /**
     * Check a value that is output as a JSON object, such as an object schema's default, which must be an array with
     * keys (or an empty one), as a list would be output as an array.
     *
     * @throws InvalidValueException - if $value is a list, other than an empty one.
     */
    public static function assertNotList(?array $value, string $fieldName) : void
    {
        if ($value !== null && $value !== [] && array_is_list($value))
        {
            throw new InvalidValueException("{$fieldName} must be an object, keyed by property name, not a list.");
        }
    }


    /**
     * Check a value that is output as a JSON array, such as an array schema's default, which must be a list.
     *
     * @throws InvalidValueException - if $value isn't a list.
     */
    public static function assertList(?array $value, string $fieldName) : void
    {
        if ($value !== null && !array_is_list($value))
        {
            throw new InvalidValueException("{$fieldName} must be a list, not an array with keys.");
        }
    }


    /**
     * Check the XML options of a schema, as "wrapped" can only be used for an array, and "attribute" can't be used for
     * an array or an object.
     *
     * @param Xml|null $xml - the XML options, if there are any.
     *
     * @param Type $type - the schema's type.
     *
     * @return void
     *
     * @throws InvalidValueException - if the options can't be used for the type.
     */
    public static function assertXmlFits(?Xml $xml, Type $type) : void
    {
        $isContainer = ($type === Type::ARRAY || $type === Type::OBJECT);

        if ($xml !== null && $xml->isWrapped() && $type !== Type::ARRAY)
        {
            throw new InvalidValueException("XML's \"wrapped\" can only be used for an array's schema.");
        }

        if ($xml !== null && $xml->isAttribute() && $isContainer)
        {
            $message = "XML's \"attribute\" can't be used for an {$type->value}, only a single value.";
            throw new InvalidValueException($message);
        }
    }


    /**
     * Check the discriminator of a oneOf or anyOf schema, whose schemas must all be references, as OpenAPI doesn't
     * consider inline schemas, and whose mappings must be to those schemas.
     *
     * @param Discriminator|null $discriminator - the discriminator, if there is one.
     *
     * @param SchemaCollection $schemas - the schemas that the value can match.
     *
     * @return void
     *
     * @throws InvalidValueException - if a schema isn't a reference, or a mapping is to a schema that isn't one of
     * them.
     */
    public static function assertDiscriminatorFits(?Discriminator $discriminator, SchemaCollection $schemas) : void
    {
        if ($discriminator !== null)
        {
            $references = [];

            foreach ($schemas as $schema)
            {
                if (!($schema instanceof SchemaReference))
                {
                    $message = "With a discriminator, every schema must be a reference (e.g. from "
                        . "NamedSchema::createReference()), as OpenAPI only considers referenced schemas.";

                    throw new InvalidValueException($message);
                }

                $references[] = $schema->getReference();
            }

            foreach ($discriminator->getMapping()->getReferences() as $reference)
            {
                if (!in_array($reference, $references, true))
                {
                    $message = "The discriminator maps a value to \"{$reference}\", which isn't one of the schemas "
                        . "that the value can match.";

                    throw new InvalidValueException($message);
                }
            }
        }
    }

}
