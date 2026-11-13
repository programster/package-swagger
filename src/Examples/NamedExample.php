<?php

namespace Programster\Swagger\Examples;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\ExampleReference;


/**
 * An example with a name, to put in an ExampleMap, along with an optional summary and description. The example can
 * be a reference to one in the components, in which case the one it points at has the summary and description.
 * https://swagger.io/specification/v3/#example-object
 */
final readonly class NamedExample implements \JsonSerializable
{
    private string $m_name;
    private ValueExample|ExternalValueExample|ExampleReference $m_example;
    private ?string $m_summary;
    private ?string $m_description;
    private ExtensionMap $m_extensions;


    /**
     * @param string $name - the name of the example, which must be unique within its ExampleMap. In the components,
     * it can only contain letters, numbers, dots, dashes and underscores.
     *
     * @param ValueExample|ExternalValueExample|ExampleReference $example - the example itself, or a reference to one.
     *
     * @param string|null $summary - a short summary of the example. This can't be given for a reference.
     *
     * @param string|null $description - a longer description of the example. CommonMark syntax may be used. This
     * can't be given for a reference.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the example. These can't be
     * given for a reference.
     *
     * @throws InvalidValueException - if the name is empty, or a reference is given a summary, description or
     * extensions, which OpenAPI would ignore.
     */
    public function __construct(
        string $name,
        ValueExample|ExternalValueExample|ExampleReference $example,
        ?string $summary = null,
        ?string $description = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($name, "The example's name");

        if (
               $example instanceof ExampleReference
            && ($summary !== null || $description !== null || !$extensions->isEmpty())
        )
        {
            $message = "The \"{$name}\" example is a reference, so it can't have a summary, description or "
                . "extensions, as OpenAPI ignores anything beside a reference. Give them to the example it points at.";

            throw new InvalidValueException($message);
        }

        $this->m_name = $name;
        $this->m_example = $example;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Create a reference to this example, for using it elsewhere, once it is in the components.
     * @return ExampleReference
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function createReference() : ExampleReference
    {
        return ExampleReference::createToComponent($this->m_name);
    }


    public function jsonSerialize() : \stdClass
    {
        if ($this->m_example instanceof ExampleReference)
        {
            $result = $this->m_example->jsonSerialize();
        }
        else
        {
            $isValue = $this->m_example instanceof ValueExample;

            $fields = [
                'summary' => $this->m_summary,
                'description' => $this->m_description,
                'value' => $isValue ? $this->m_example : null,
                'externalValue' => $isValue ? null : $this->m_example->getUrl(),
            ];

            $result = JsonObject::create($fields, $this->m_extensions);
        }

        return $result;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getExample() : ValueExample|ExternalValueExample|ExampleReference { return $this->m_example; }
    public function getSummary() : ?string { return $this->m_summary; }
    public function getDescription() : ?string { return $this->m_description; }
}
