<?php

namespace Programster\Swagger\Schemas;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\Reference;


/**
 * A property of an object's schema (SchemaObject).
 */
final readonly class Property implements \JsonSerializable
{
    private string $m_name;
    private SchemaInterface $m_schema;
    private bool $m_required;
    private Access $m_access;


    /**
     * @param string $name - the name of the property.
     *
     * @param SchemaInterface $schema - the schema of the property's value, or a reference to one.
     *
     * @param bool $required - true if every object must have this property. A read-only property is only required in
     * responses, and a write-only one only in requests.
     *
     * @param Access $access - whether the property is in requests, responses or both, e.g. Access::READ_ONLY for an
     * ID that the server sets.
     *
     * @throws InvalidValueException - if the name is empty.
     */
    public function __construct(
        string $name,
        SchemaInterface $schema,
        bool $required = false,
        Access $access = Access::READ_WRITE
    )
    {
        Validator::assertNotEmpty($name, "The property's name");
        $this->m_name = $name;
        $this->m_schema = $schema;
        $this->m_required = $required;
        $this->m_access = $access;
    }


    /**
     * Get the property's schema, marked as read-only or write-only if it is. As OpenAPI ignores anything beside a
     * reference, a reference is wrapped in an allOf to mark it.
     * @return \stdClass
     */
    public function jsonSerialize() : \stdClass
    {
        $accessField = match ($this->m_access) {
            Access::READ_WRITE => null,
            Access::READ_ONLY => 'readOnly',
            Access::WRITE_ONLY => 'writeOnly',
        };

        if ($accessField === null)
        {
            $result = $this->m_schema->jsonSerialize();
        }
        elseif ($this->m_schema instanceof Reference)
        {
            $result = (object)['allOf' => [$this->m_schema], $accessField => true];
        }
        else
        {
            $result = $this->m_schema->jsonSerialize();
            $result->{$accessField} = true;
        }

        return $result;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getSchema() : SchemaInterface { return $this->m_schema; }
    public function isRequired() : bool { return $this->m_required; }
    public function getAccess() : Access { return $this->m_access; }
}
