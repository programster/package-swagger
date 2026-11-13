<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A link from a response to another operation, which can be called with values from the response, e.g. getting the
 * user that a "create user" response returned the ID of. Create one with createFromOperationId() or
 * createFromOperationRef(), as it must point at the operation one way or the other.
 * https://swagger.io/specification/v3/#link-object
 */
final readonly class Link implements LinkInterface
{
    private ?string $m_operationRef;
    private ?string $m_operationId;
    private ?array $m_parameters;
    private mixed $m_requestBody;
    private ?string $m_description;
    private ?Server $m_server;
    private ExtensionMap $m_extensions;


    private function __construct(
        ?string $operationRef,
        ?string $operationId,
        ?array $parameters,
        mixed $requestBody,
        ?string $description,
        ?Server $server,
        ExtensionMap $extensions
    )
    {
        $this->m_operationRef = $operationRef;
        $this->m_operationId = $operationId;
        $this->m_parameters = $parameters;
        $this->m_requestBody = $requestBody;
        $this->m_description = $description;
        $this->m_server = $server;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Create a link to an operation in this document, by its operationId.
     *
     * @param string $operationId - the operationId of the operation, e.g. "getUser".
     *
     * @param array|null $parameters - the parameters to call it with, under their names, e.g.
     * ['userId' => '$response.body#/id']. Each value is a constant, or a runtime expression. A name can be prefixed
     * with the parameter's location (e.g. "path.id") when two have the same name.
     *
     * @param mixed $requestBody - a value, or runtime expression, to use as the request body.
     *
     * @param string|null $description - a description of the link. CommonMark syntax may be used.
     *
     * @param Server|null $server - the server to call the operation on, if not the usual one.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the link.
     *
     * @return Link
     *
     * @throws InvalidValueException - if the operationId is empty.
     */
    public static function createFromOperationId(
        string $operationId,
        ?array $parameters = null,
        mixed $requestBody = null,
        ?string $description = null,
        ?Server $server = null,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Link
    {
        Validator::assertNotEmpty($operationId, "The link's operationId");
        return new Link(null, $operationId, $parameters, $requestBody, $description, $server, $extensions);
    }


    /**
     * Create a link to an operation by a reference to it, which may be in another document.
     *
     * @param string $operationRef - the reference to the operation, e.g. "#/paths/~1users~1{id}/get", or
     * "https://example.com/openapi.json#/paths/~1users/post".
     *
     * @param array|null $parameters - the parameters to call it with, under their names, e.g.
     * ['userId' => '$response.body#/id']. Each value is a constant, or a runtime expression. A name can be prefixed
     * with the parameter's location (e.g. "path.id") when two have the same name.
     *
     * @param mixed $requestBody - a value, or runtime expression, to use as the request body.
     *
     * @param string|null $description - a description of the link. CommonMark syntax may be used.
     *
     * @param Server|null $server - the server to call the operation on, if not the usual one.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the link.
     *
     * @return Link
     *
     * @throws InvalidValueException - if the operationRef is empty.
     */
    public static function createFromOperationRef(
        string $operationRef,
        ?array $parameters = null,
        mixed $requestBody = null,
        ?string $description = null,
        ?Server $server = null,
        ExtensionMap $extensions = new ExtensionMap()
    ) : Link
    {
        Validator::assertNotEmpty($operationRef, "The link's operationRef");
        return new Link($operationRef, null, $parameters, $requestBody, $description, $server, $extensions);
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'operationRef' => $this->m_operationRef,
            'operationId' => $this->m_operationId,
            'parameters' => $this->m_parameters,
            'requestBody' => $this->m_requestBody,
            'description' => $this->m_description,
            'server' => $this->m_server,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
