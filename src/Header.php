<?php

/*
 * https://swagger.io/specification/#header-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Examples\ExternalValueExample;
use Programster\Swagger\Examples\ValueExample;


class Header implements \JsonSerializable
{
    /**
     * Create a header.
     * @param string $description - the description for this header.
     * @param Type $type - the type. E.g. integer/string
     */
    private function __construct(
        private readonly string $name,
        private readonly ?ContentMap $content = null,
        private readonly ?bool $required = null,
        private readonly SchemaInterface | Reference | null $schema,
        private readonly ?string $description = null,
        private readonly ?bool $deprecated = null,
        private readonly ?bool $allowEmptyValue = null,
        private readonly ?bool $explode = null,
        private readonly ?bool $allowReserved = null,
        private readonly mixed $example = null,
        private readonly ?ExampleMap $examples = null
    )
    {
    }


    /**
     * Creates a parameter object with the help of a provided Schema (or reference to a schema).
     * @param string $name
     * @param bool $required
     * @param SchemaInterface|Reference $schema
     * @param string|null $description
     * @param bool|null $deprecated
     * @param bool|null $allowEmptyValue
     * @param bool|null $explode
     * @param bool|null $allowReserved
     * @param mixed|null $example
     * @return Parameter
     * @throws \Exception
     */
    public static function createFromSchema(
        string $name,
        SchemaInterface | Reference $schema,
        ?bool $required = null,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        ExampleMap | ExternalValueExample | ValueExample | null $example = null
    ) : Header
    {
        if ($example === null)
        {
            $examples = null;
        }
        else
        {
            if (get_class($example) === ExampleMap::class)
            {
                $examples = $example->toArray();
                $example = null;
            }
            else
            {
                $examples = null;
            }
        }

        return new Header(
            name: $name,
            content: null,
            required: $required,
            schema: $schema,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            explode: $explode,
            allowReserved: $allowReserved,
            example: $example,
            examples: $examples
        );
    }


    /**
     * Creates a parameter object with the help of a provided ContentMap object.
     * @param string $name
     * @param bool $required
     * @param ContentMap $content
     * @param string|null $description
     * @param bool|null $deprecated
     * @param bool|null $allowEmptyValue
     * @return Parameter
     * @throws \Exception
     */
    public static function createFromContentMap(
        string $name,
        ContentMap $content,
        ?bool $required = null,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null
    ) : Header
    {
        return new Header(
            name: $name,
            content: $content,
            required: $required,
            schema: null,
            description: $description,
            deprecated: $deprecated,
            allowEmptyValue: $allowEmptyValue,
            explode: null,
            allowReserved: null,
            example: null,
            examples: null,
        );
    }


    public function toArray()
    {
        $arrayForm = array();

        if ($this->required !== null) { $arrayForm['required'] = $this->required; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->deprecated !== null) { $arrayForm['deprecated'] = $this->deprecated; }
        if ($this->allowEmptyValue !== null) { $arrayForm['allowEmptyValue'] = $this->allowEmptyValue; }
        if ($this->schema !== null) { $arrayForm['schema'] = $this->schema; }
        if ($this->content !== null) { $arrayForm['content'] = $this->content; }
        if ($this->explode !== null) { $arrayForm['explode'] = $this->explode; }
        if ($this->allowReserved !== null) { $arrayForm['allowReserved'] = $this->allowReserved; }
        if ($this->example !== null) { $arrayForm['example'] = $this->example; }
        if ($this->examples !== null) { $arrayForm['examples'] = $this->examples; }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    # Accessors
    public function getName() : string { return $this->name; }
}
