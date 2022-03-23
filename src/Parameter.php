<?php

/*
 * https://swagger.io/specification/#parameter-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;

class Parameter implements \JsonSerializable
{
    /**
     * https://swagger.io/specification/#parameter-object
     * @param string $name
     * @param ParameterLocation $in
     * @param bool $required
     * @param Schema|ContentMap $contentOrSchema
     * @param string|null $description
     * @param bool|null $deprecated
     * @param bool|null $allowEmptyValue
     * @throws \Exception - if allowEmptyValue set to true, but parameter is not located in the query.
     */
    private function __construct(
        private readonly string $name,
        private readonly ParameterLocation $in,
        private readonly bool $required,
        private readonly ContentMap $content,
        private readonly SchemaString | SchemaNumber | SchemaArray | SchemaInt | SchemaObject | Reference $schema,
        private readonly ?string $description = null,
        private readonly ?bool $deprecated = null,
        private readonly ?bool $allowEmptyValue = null,
        private readonly ?Style $style = null,
        private readonly ?bool $explode = null,
        private readonly ?bool $allowReserved = null,
        private readonly mixed $example = null,
        private readonly ?ExampleMap $examples = null
    )
    {
        if ($in !== ParameterLocation::QUERY && $this->allowEmptyValue === true)
        {
            throw new \Exception("allowEmptyValue can only be set to true for parameters located in the query");
        }

        if ($style !== null)
        {
            switch ($style)
            {
                case Style::MATRIX:
                {
                    if ($this->in !== ParameterLocation::PATH)
                    {
                        throw new Exception("The matrix style is only applicable to parameters in the 'path'");
                    }
                }
                break;

                case Style::LABEL:
                {
                    if ($this->in !== ParameterLocation::PATH)
                    {
                        throw new Exception("The label style is only applicable to parameters in the 'path'");
                    }
                }
                break;

                case Style::FORM:
                {
                    if (!in_array($this->in, [ParameterLocation::QUERY, ParameterLocation::COOKIE]))
                    {
                        throw new Exception("The form style is only applicable to parameters in the 'query' or 'cookie'");
                    }
                }
                break;

                case Style::SIMPLE:
                {
                    if (!in_array($this->in, [ParameterLocation::PATH, ParameterLocation::HEADER]))
                    {
                        throw new Exception("The SIMPLE style is only applicable to parameters in the 'path' or 'header'");
                    }
                }
                break;

                case Style::SPACE_DELIMITED:
                {
                    if (!in_array($this->in, [ParameterLocation::QUERY]))
                    {
                        throw new Exception("The SPACE_DELIMITED style is only applicable to parameters in the 'query'");
                    }
                }
                break;

                case Style::PIPE_DELIMITED:
                {
                    if (!in_array($this->in, [ParameterLocation::QUERY]))
                    {
                        throw new Exception("The PIPE_DELIMITED style is only applicable to parameters in the 'query'");
                    }
                }
                break;

                case Style::DEEP_OBJECT:
                {
                    if (!in_array($this->in, [ParameterLocation::QUERY]))
                    {
                        throw new Exception("The DEEP_OBJECT style is only applicable to parameters in the 'query'");
                    }
                }
                break;

                default:
                {
                    throw new Exception("Unrecognized style: " . $this->style->value);
                }
            }
        }
    }


    /**
     * Creates a parameter object with the help of a provided Schema (or reference to a schema).
     * @param string $name
     * @param ParameterLocation $in
     * @param bool $required
     * @param Schema $schema
     * @param string|null $description
     * @param bool|null $deprecated
     * @param bool|null $allowEmptyValue
     * @param string|null $style
     * @param bool|null $explode
     * @param bool|null $allowReserved
     * @param mixed|null $example
     * @return Parameter
     * @throws \Exception
     */
    public static function createFromSchema(
        string $name,
        ParameterLocation $in,
        bool $required,
        SchemaString | SchemaNumber | SchemaArray | SchemaInt | SchemaObject | Reference $schema,
        ?string $description = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        ?string $style = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        mixed $example = null
    )
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

        return new Parameter(
            $name,
            $in,
            $required,
            null,
            $schema,
            $description,
            $deprecated,
            $allowEmptyValue,
            $style,
            $explode,
            $allowReserved,
            $example,
            $examples
        );
    }


    /**
     * Creates a parameter object with the help of a provided ContentMap object.
     * @param string $name
     * @param ParameterLocation $in
     * @param bool $required
     * @param ContentMap $content
     * @param string|null $description
     * @param bool|null $deprecated
     * @param bool|null $allowEmptyValue
     * @return Parameter
     * @throws \Exception
     */
    public static function createFromContentMap(
        string            $name,
        ParameterLocation $in,
        bool              $required,
        ContentMap        $content,
        ?string           $description = null,
        ?bool             $deprecated = null,
        ?bool             $allowEmptyValue = null
    )
    {
        return new Parameter(
            $name,
            $in,
            $required,
            $content,
            null,
            $description,
            $deprecated,
            $allowEmptyValue,
            null,
            null,
            null,
            null,
            null
        );
    }


    public function toArray()
    {
        $arrayForm = array(
            'name' => $this->m_name,
            'in' => $this->m_in,
            'required' => $this->m_required,
        );

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->deprecated !== null) { $arrayForm['deprecated'] = $this->deprecated; }
        if ($this->allowEmptyValue !== null) { $arrayForm['allowEmptyValue'] = $this->allowEmptyValue; }
        if ($this->schema !== null) { $arrayForm['schema'] = $this->schema; }
        if ($this->content !== null) { $arrayForm['content'] = $this->content; }
        if ($this->style !== null) { $arrayForm['style'] = $this->style; }
        if ($this->explode !== null) { $arrayForm['explode'] = $this->explode; }
        if ($this->allowReserved !== null) { $arrayForm['allowReserved'] = $this->allowReserved; }
        if ($this->example !== null) { $arrayForm['example'] = $this->example; }
        if ($this->examples !== null) { $arrayForm['examples'] = $this->examples; }

        return $arrayForm;
    }


    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}

