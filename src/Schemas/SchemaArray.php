<?php

/*
 * http://json-schema.org/understanding-json-schema/reference/array.html?highlight=array
 */

namespace Programster\Swagger\Schemas;


class SchemaArray implements SchemaInterface, \JsonSerializable
{
    /**
     * http://json-schema.org/understanding-json-schema/reference/array.html?highlight=array
     *
     * @param string|null $description
     *
     * @param bool|null $required
     *
     * @param int|null $minItems
     *
     * @param int|null $maxItems
     *
     * @param Type|null $items - List validation is useful for arrays of arbitrary length where each item matches the
     * same schema. For this kind of array, set the items keyword to a single schema that will be used to validate all
     * of the items in the array.
     *
     * @param bool|null $uniqueItems - Set to true if each of the items in an array should be unique.
     *
     * @param Type|null $contains - While the items schema must be valid for every item in the array, the contains
     * schema only needs to validate against one or more items in the array.
     *
     * @param int|null $minContains - can be used with contains to further specify how many times a schema matches a
     * contains constraint. These keywords can be any non-negative number including zero.
     *
     * @param int|null $maxContains can be used with contains to further specify how many times a schema matches a
     * contains constraint. These keywords can be any non-negative number including zero.
     */
    public function __construct(
        private readonly ?string $description = null,
        private readonly ?bool $required = null,
        private readonly ?int $minItems = null,
        private readonly ?int $maxItems = null,
        private readonly ?Type $items = null,
        private readonly ?bool $uniqueItems = null,
        private readonly ?Type $contains = null,
        private readonly ?int $minContains = null,
        private readonly ?int $maxContains = null
    )
    {
        if ($this->minContains < 0)
        {
            throw new \Exception("minContains cannot be less than 0");
        }

        if ($this->maxContains < 0)
        {
            throw new \Exception("maxContains cannot be less than 0");
        }
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('type' => Type::ARRAY);

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->required !== null) { $arrayForm['required'] = $this->required; }
        if ($this->minItems !== null) { $arrayForm['minItems'] = $this->minItems; }
        if ($this->maxItems !== null) { $arrayForm['maxItems'] = $this->maxItems; }
        if ($this->contains !== null) { $arrayForm['contains'] = $this->contains; }
        if ($this->minContains !== null) { $arrayForm['minContains'] = $this->minContains; }
        if ($this->maxContains !== null) { $arrayForm['maxContains'] = $this->maxContains; }
        if ($this->items !== null) { $arrayForm['items'] = array('type' => $this->items); }

        return $arrayForm;
    }
}
