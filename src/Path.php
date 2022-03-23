<?php

/*
 * https://swagger.io/specification/#path-item-object
 */

namespace Programster\Swagger;


class Path implements \JsonSerializable
{
    /**
     * Define a path.
     *
     * @param string $relativePath - The URL path for this path. E.g. "/users"
     *
     * @param ?OperationCollection $operations = null - The operations under this path. E.g. GET, POST, PUT, etc.
     *
     * @param string|null $ref - Allows for an external definition of this path item. The referenced structure MUST be
     * in the format of a Path Item Object. In case a Path Item Object field appears both in the defined object and the
     * referenced object, the behavior is undefined.
     *
     * @param string|null $summary - An optional, string summary, intended to apply to all operations in this path.
     *
     * @param string|null $description - An optional, string description, intended to apply to all operations in this
     * path. CommonMark syntax MAY be used for rich text representation.
     *
     * @param ServerCollection|null $servers - An alternative server array to service all operations in this path.
     *
     * @param ParameterCollection|null $parameters - A list of parameters that are applicable for all the operations
     * described under this path. These parameters can be overridden at the operation level, but cannot be removed
     * there. The list MUST NOT include duplicated parameters. A unique parameter is defined by a combination of a name
     * and location. The list can use the Reference Object to link to parameters that are defined at the OpenAPI
     * Object's components/parameters.
     *
     * @throws \Exception
     */
    public function __construct(
        private readonly string $relativePath,
        private readonly ?OperationCollection $operations,
        private readonly ?string $ref = null,
        private readonly ?string $summary = null,
        private readonly ?string $description = null,
        private readonly ?ServerCollection $servers = null,
        private readonly ?ParameterCollection $parameters = null,
    )
    {
    }


    public function toArray() : array
    {
        $arrayForm = [];

        if ($this->ref !== null) { $arrayForm['#ref'] = $this->ref; }
        if ($this->summary !== null) { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->servers !== null) { $arrayForm['servers'] = $this->servers; }
        if ($this->parameters !== null) { $arrayForm['parameters'] = $this->parameters; }

        foreach ($this->operations as $operation)
        {
            /* @var $operation \Programster\Swagger\Operation */
            $arrayForm[$operation->getMethod()->value] = $operation;
        }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    public function getRelativePath() : string
    {
        return $this->relativePath;
    }
}
