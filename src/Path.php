<?php

/*
 * https://swagger.io/specification/#path-item-object
 */

namespace Programster\Swagger;


class Path implements \JsonSerializable
{
    private readonly string $m_relativePath;
    private readonly ?OperationCollection $m_operations;
    private readonly ?string $m_ref;
    private readonly ?string $m_summary;
    private readonly ?string $m_description;
    private readonly ?ServerCollection $m_servers;
    private readonly ?ParameterCollection $m_parameters;


    /**
     * Define a path.
     *
     * @param string $relativePath - The URL path for this path. E.g. "/users"
     *
     * @param OperationCollection|null $operations - The operations under this path. E.g. GET, POST, PUT, etc.
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
     */
    public function __construct(
        string $relativePath,
        ?OperationCollection $operations,
        ?string $ref = null,
        ?string $summary = null,
        ?string $description = null,
        ?ServerCollection $servers = null,
        ?ParameterCollection $parameters = null,
    )
    {
        $this->m_relativePath = $relativePath;
        $this->m_operations = $operations;
        $this->m_ref = $ref;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_servers = $servers;
        $this->m_parameters = $parameters;
    }


    public function toArray() : array
    {
        $optionalFields = [
            '$ref' => $this->m_ref,
            'summary' => $this->m_summary,
            'description' => $this->m_description,
            'servers' => $this->m_servers,
            'parameters' => $this->m_parameters,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        $arrayForm = array_filter($optionalFields, $isProvided);

        if ($this->m_operations !== null)
        {
            foreach ($this->m_operations as $operation)
            {
                /* @var $operation Operation */
                $arrayForm[$operation->getMethod()->value] = $operation;
            }
        }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    # Accessors
    public function getRelativePath() : string { return $this->m_relativePath; }
}
