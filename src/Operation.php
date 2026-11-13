<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Security\SecurityRequirementCollection;


/**
 * An operation on a path, e.g. GET /users.
 * https://swagger.io/specification/v3/#operation-object
 */
final readonly class Operation implements \JsonSerializable
{
    private Method $m_method;
    private ResponseCollection $m_responses;
    private ?string $m_summary;
    private ?string $m_description;
    private ParameterCollection $m_parameters;
    private ?RequestBodyInterface $m_requestBody;
    private array $m_tags;
    private ?string $m_operationId;
    private ?ExternalDocumentation $m_externalDocs;
    private CallbackMap $m_callbacks;
    private bool $m_deprecated;
    private ?SecurityRequirementCollection $m_security;
    private ServerCollection $m_servers;
    private ExtensionMap $m_extensions;


    /**
     * @param Method $method - the HTTP method, e.g. Method::GET.
     *
     * @param ResponseCollection $responses - the responses the operation can give. There must be at least one.
     *
     * @param string|null $summary - a short summary of what the operation does.
     *
     * @param string|null $description - a longer description of what the operation does. CommonMark syntax may be
     * used.
     *
     * @param ParameterCollection $parameters - the parameters the operation takes, as well as the path's.
     *
     * @param RequestBodyInterface|null $requestBody - the body of the request, or a reference to one. GET, HEAD,
     * DELETE and TRACE operations can't have one, as HTTP doesn't define what a body means for them.
     *
     * @param string[] $tags - the names of the tags to group the operation under, e.g. ["users"].
     *
     * @param string|null $operationId - a unique ID for the operation, e.g. "getUser", which links can use to point
     * at it. It must be unique within the document.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the operation.
     *
     * @param CallbackMap $callbacks - requests that the API may make back to the client because of this operation.
     *
     * @param bool $deprecated - true if the operation is deprecated, and shouldn't be used.
     *
     * @param SecurityRequirementCollection|null $security - the security for this operation, instead of the
     * document's. Null (the default) uses the document's. An empty collection makes the operation public.
     *
     * @param ServerCollection $servers - the servers this operation is on, instead of the path's or document's.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the operation.
     *
     * @throws InvalidValueException - if there are no responses, a tag isn't a string, or the operation can't have a
     * request body.
     */
    public function __construct(
        Method $method,
        ResponseCollection $responses,
        ?string $summary = null,
        ?string $description = null,
        ParameterCollection $parameters = new ParameterCollection(),
        ?RequestBodyInterface $requestBody = null,
        array $tags = [],
        ?string $operationId = null,
        ?ExternalDocumentation $externalDocs = null,
        CallbackMap $callbacks = new CallbackMap(),
        bool $deprecated = false,
        ?SecurityRequirementCollection $security = null,
        ServerCollection $servers = new ServerCollection(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $methodName = strtoupper($method->value);

        if ($responses->isEmpty())
        {
            throw new InvalidValueException("The {$methodName} operation needs at least one response.");
        }

        $methodsWithoutBodies = [Method::GET, Method::HEAD, Method::DELETE, Method::TRACE];

        if ($requestBody !== null && in_array($method, $methodsWithoutBodies, true))
        {
            $message = "A {$methodName} operation can't have a request body, as HTTP doesn't define what one means "
                . "for it.";

            throw new InvalidValueException($message);
        }

        foreach ($tags as $tag)
        {
            if (!is_string($tag))
            {
                $type = get_debug_type($tag);
                throw new InvalidValueException("Tags must be the names of the tags, but one was a {$type}.");
            }
        }

        $this->m_method = $method;
        $this->m_responses = clone $responses;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_parameters = clone $parameters;
        $this->m_requestBody = $requestBody;
        $this->m_tags = array_values(array_unique($tags));
        $this->m_operationId = $operationId;
        $this->m_externalDocs = $externalDocs;
        $this->m_callbacks = clone $callbacks;
        $this->m_deprecated = $deprecated;
        $this->m_security = ($security === null) ? null : clone $security;
        $this->m_servers = clone $servers;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Get the operation's own security, if it has any. Null means it uses the document's.
     * @return SecurityRequirementCollection|null
     */
    public function getSecurity() : ?SecurityRequirementCollection
    {
        return ($this->m_security === null) ? null : clone $this->m_security;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'tags' => count($this->m_tags) === 0 ? null : $this->m_tags,
            'summary' => $this->m_summary,
            'description' => $this->m_description,
            'externalDocs' => $this->m_externalDocs,
            'operationId' => $this->m_operationId,
            'parameters' => $this->m_parameters->isEmpty() ? null : $this->m_parameters,
            'requestBody' => $this->m_requestBody,
            'responses' => $this->m_responses,
            'callbacks' => $this->m_callbacks->isEmpty() ? null : $this->m_callbacks,
            'deprecated' => $this->m_deprecated ? true : null,
            'security' => $this->m_security,
            'servers' => $this->m_servers->isEmpty() ? null : $this->m_servers,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getMethod() : Method { return $this->m_method; }
    public function getOperationId() : ?string { return $this->m_operationId; }
    public function getParameters() : ParameterCollection { return clone $this->m_parameters; }
}
