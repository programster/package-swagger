<?php

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Security\SecuritySchemeMap;

class Components implements \JsonSerializable
{
    private readonly ?SchemaMap $m_schemas;
    private readonly ?ResponseCollection $m_responses;
    private readonly ?ParameterMap $m_parameters;
    private readonly ?ExampleMap $m_examples;
    private readonly ?RequestBodyMap $m_requestBodies;
    private readonly ?HeaderMap $m_headers;
    private readonly ?SecuritySchemeMap $m_securitySchemes;
    private readonly ?LinkMap $m_links;
    private readonly ?Callbacks $m_callbacks;


    public function __construct(
        ?SchemaMap $schemas = null,
        ?ResponseCollection $responses = null,
        ?ParameterMap $parameters = null,
        ?ExampleMap $examples = null,
        ?RequestBodyMap $requestBodies = null,
        ?HeaderMap $headers = null,
        ?SecuritySchemeMap $securitySchemes = null,
        ?LinkMap $links = null,
        ?Callbacks $callbacks = null,
    )
    {
        $this->m_schemas = $schemas;
        $this->m_responses = $responses;
        $this->m_parameters = $parameters;
        $this->m_examples = $examples;
        $this->m_requestBodies = $requestBodies;
        $this->m_headers = $headers;
        $this->m_securitySchemes = $securitySchemes;
        $this->m_links = $links;
        $this->m_callbacks = $callbacks;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'schemas' => $this->m_schemas,
            'responses' => $this->m_responses,
            'parameters' => $this->m_parameters,
            'examples' => $this->m_examples,
            'requestBodies' => $this->m_requestBodies,
            'headers' => $this->m_headers,
            'securitySchemes' => $this->m_securitySchemes,
            'links' => $this->m_links,
            'callbacks' => $this->m_callbacks,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_filter($optionalFields, $isProvided);
    }
}
