<?php

/*
 * https://swagger.io/specification/#operation-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;

class Operation implements \JsonSerializable
{
    private readonly Method $m_method;
    private readonly ResponseCollection $m_responses;
    private readonly ?string $m_summary;
    private readonly ?string $m_description;
    private readonly ?ParameterCollection $m_parameters;
    private readonly RequestBody|Reference|null $m_requestBody;
    private readonly ?array $m_tags;
    private readonly ?string $m_operationId;
    private readonly ?string $m_externalDocs;
    private readonly ?Callbacks $m_callbacks;
    private readonly ?bool $m_deprecated;
    private readonly SecurityRequirementCollection|SecurityRequirement|null $m_security;
    private readonly ?ServerCollection $m_servers;


    /**
     * https://swagger.io/specification/#operation-object
     * @param Method $method - the HTTP method, e.g. Method::GET.
     * @param ResponseCollection $responses - the possible responses. There must be at least one.
     * @param string|null $summary - a short summary of what the operation does.
     * @param string|null $description - a longer description of what the operation does.
     * @param ParameterCollection|null $parameters - the parameters that the operation takes.
     * @param RequestBody|Reference|null $requestBody - the request body, or a reference to one.
     * @param string[]|null $tags - the names of the tags to group the operation under.
     * @param string|null $operationId - a unique ID for the operation.
     * @param string|null $externalDocs - a link to external documentation for the operation.
     * @param Callbacks|null $callbacks - the callbacks for the operation.
     * @param bool|null $deprecated - whether the operation is deprecated.
     * @param SecurityRequirementCollection|SecurityRequirement|null $security - the security for the operation.
     * @param ServerCollection|null $servers - the servers that handle the operation.
     * @throws \Exception - if there are no responses, or one of the tags isn't a string.
     */
    public function __construct(
        Method $method,
        ResponseCollection $responses,
        ?string $summary = null,
        ?string $description = null,
        ?ParameterCollection $parameters = null,
        RequestBody|Reference|null $requestBody = null,
        ?array $tags = null,
        ?string $operationId = null,
        ?string $externalDocs = null,
        ?Callbacks $callbacks = null,
        ?bool $deprecated = null,
        SecurityRequirementCollection|SecurityRequirement|null $security = null,
        ?ServerCollection $servers = null
    )
    {
        if (count($responses->toArray()) === 0)
        {
            throw new \Exception("Paths need at least one response");
        }

        if ($tags !== null)
        {
            foreach ($tags as $tag)
            {
                if (is_string($tag) === false)
                {
                    $tagType = get_debug_type($tag);
                    throw new \Exception("Tags need to be strings, but one of them is of type {$tagType}");
                }
            }
        }

        $this->m_method = $method;
        $this->m_responses = $responses;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_parameters = $parameters;
        $this->m_requestBody = $requestBody;
        $this->m_tags = $tags;
        $this->m_operationId = $operationId;
        $this->m_externalDocs = $externalDocs;
        $this->m_callbacks = $callbacks;
        $this->m_deprecated = $deprecated;
        $this->m_security = $security;
        $this->m_servers = $servers;
    }


    public function jsonSerialize() : mixed
    {
        $optionalFields = [
            'summary' => $this->m_summary,
            'description' => $this->m_description,
            'parameters' => $this->m_parameters,
            'requestBody' => $this->m_requestBody,
            'tags' => $this->m_tags,
            'operationId' => $this->m_operationId,
            'externalDocs' => $this->m_externalDocs,
            'callbacks' => $this->m_callbacks,
            'deprecated' => $this->m_deprecated,
            'security' => $this->m_security,
            'servers' => $this->m_servers,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge(['responses' => $this->m_responses], array_filter($optionalFields, $isProvided));
    }


    # Accessors
    public function getMethod() : Method { return $this->m_method; }
}
