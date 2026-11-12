<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/array.html?highlight=array
 */

namespace Programster\Swagger\Schemas;


use Programster\Swagger\Type;

class SchemaArray implements SchemaInterface, \JsonSerializable
{
    private readonly ?string $m_description;
    private readonly ?int $m_minItems;
    private readonly ?int $m_maxItems;
    private readonly ?SchemaInterface $m_items;
    private readonly ?bool $m_uniqueItems;
    private readonly ?int $m_minContains;
    private readonly ?int $m_maxContains;


    /**
     * http://json-schema.org/understanding-json-schema/reference/array.html?highlight=array
     *
     * @param string|null $description - a description of the array.
     *
     * @param int|null $minItems - the minimum number of items in the array.
     *
     * @param int|null $maxItems - the maximum number of items in the array.
     *
     * @param SchemaInterface|null $items - List validation is useful for arrays of arbitrary length where each item
     * matches the same schema. For this kind of array, set the items keyword to a single schema that will be used to
     * validate all of the items in the array.
     *
     * @param bool|null $uniqueItems - Set to true if each of the items in an array should be unique.
     *
     * @param int|null $minContains - can be used with contains to further specify how many times a schema matches a
     * contains constraint. These keywords can be any non-negative number including zero.
     *
     * @param int|null $maxContains - can be used with contains to further specify how many times a schema matches a
     * contains constraint. These keywords can be any non-negative number including zero.
     *
     * @throws \Exception - if $minContains or $maxContains is less than 0.
     */
    public function __construct(
        ?string $description = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        ?SchemaInterface $items = null,
        ?bool $uniqueItems = null,
        // "contains" is not supported in OpenApi 3, only JSON schema.
        ?int $minContains = null,
        ?int $maxContains = null
    )
    {
        if ($minContains !== null && $minContains < 0)
        {
            throw new \Exception("minContains cannot be less than 0");
        }

        if ($maxContains !== null && $maxContains < 0)
        {
            throw new \Exception("maxContains cannot be less than 0");
        }

        $this->m_description = $description;
        $this->m_minItems = $minItems;
        $this->m_maxItems = $maxItems;
        $this->m_items = $items;
        $this->m_uniqueItems = $uniqueItems;
        $this->m_minContains = $minContains;
        $this->m_maxContains = $maxContains;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'description' => $this->m_description,
            'minItems' => $this->m_minItems,
            'maxItems' => $this->m_maxItems,
            'uniqueItems' => $this->m_uniqueItems,
            'minContains' => $this->m_minContains,
            'maxContains' => $this->m_maxContains,
            'items' => $this->m_items,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['type' => Type::ARRAY], array_filter($optionalFields, $isProvided));
    }
}
