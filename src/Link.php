<?php

/*
 * https://swagger.io/specification/#link-object
 */

namespace Programster\Swagger;


class Link implements \JsonSerializable
{
    private readonly ?string $m_operationRef;
    private readonly ?string $m_operationId;
    private readonly ?array $m_parameters;
    private readonly mixed $m_requestBody;
    private readonly ?string $m_description;
    private readonly ?Server $m_server;


    /**
     * The Link object represents a possible design-time link for a response. The presence of a link does not guarantee
     * the caller's ability to successfully invoke it, rather it provides a known relationship and traversal mechanism
     * between responses and other operations.
     * Unlike dynamic links (i.e. links provided in the response payload), the OAS linking mechanism does not require
     * link information in the runtime response.
     * For computing links, and providing instructions to execute them, a runtime expression is used for accessing
     * values in an operation and using them as parameters while invoking the linked operation.
     *
     * @param string|null $operationRef - A relative or absolute URI reference to an OAS operation. This field is
     * mutually exclusive of the operationId field, and MUST point to an Operation Object. Relative operationRef values
     * MAY be used to locate an existing Operation Object in the OpenAPI definition.
     *
     * @param string|null $operationId - The name of an existing, resolvable OAS operation, as defined with a unique
     * operationId. This field is mutually exclusive of the operationRef field.
     *
     * @param array|null $parameters - A map representing parameters to pass to an operation as specified with
     * operationId or identified via operationRef. The key is the parameter name to be used, whereas the value can be a
     * constant or an expression to be evaluated and passed to the linked operation. The parameter name can be qualified
     * using the parameter location [{in}.]{name} for operations that use the same parameter name in different locations
     * (e.g. path.id).
     *
     * @param mixed $requestBody - A literal value or {expression} to use as a request body when calling the target
     * operation.
     *
     * @param string|null $description - A description of the link. CommonMark syntax MAY be used for rich text
     * representation.
     *
     * @param Server|null $server - A server object to be used by the target operation.
     */
    public function __construct(
        ?string $operationRef,
        ?string $operationId,
        ?array $parameters,
        mixed $requestBody,
        ?string $description,
        ?Server $server
    )
    {
        $this->m_operationRef = $operationRef;
        $this->m_operationId = $operationId;
        $this->m_parameters = $parameters;
        $this->m_requestBody = $requestBody;
        $this->m_description = $description;
        $this->m_server = $server;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'operationRef' => $this->m_operationRef,
            'operationId' => $this->m_operationId,
            'parameters' => $this->m_parameters,
            'requestBody' => $this->m_requestBody,
            'description' => $this->m_description,
            'server' => $this->m_server,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_filter($optionalFields, $isProvided);
    }
}
