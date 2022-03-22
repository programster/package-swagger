<?php

/*
 * https://swagger.io/specification/#parameter-object
 */

namespace Programster\Swagger;

class Parameter implements \JsonSerializable
{
    private readonly ?Schema $schema;
    private readonly ?ContentMap $content;


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
    public function __construct(
        private readonly string $name,
        private readonly ParameterLocation $in,
        private readonly bool $required,
        Schema | ContentMap $contentOrSchema,
        private readonly ?string $description = null,
        private readonly ?bool $deprecated = null,
        private readonly ?bool $allowEmptyValue = null
    )
    {
        if ($in !== ParameterLocation::QUERY && $this->allowEmptyValue === true)
        {
            throw new \Exception("allowEmptyValue can only be set to true for parameters located in the query");
        }
    }


    public function toArray()
    {
        $arrayForm = array(
            'name' => $this->m_name,
            'in' => $this->m_in,
            'required' => $this->m_required,
        );

        if ($this->description !== null) {$arrayForm['description'] = $this->description; }
        if ($this->deprecated !== null) {$arrayForm['deprecated'] = $this->deprecated; }
        if ($this->allowEmptyValue !== null) {$arrayForm['allowEmptyValue'] = $this->allowEmptyValue; }
        if ($this->schema !== null) {$arrayForm['schema'] = $this->schema; }
        if ($this->content !== null) {$arrayForm['content'] = $this->content; }

        return $arrayForm;
    }


    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}

