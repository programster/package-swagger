<?php

/*
 * https://swagger.io/specification/#operation-object
 */

namespace Programster\Swagger;

use Programster\Swagger\Security\ApiKey;
use Programster\Swagger\Security\HttpBasicAuth;
use Programster\Swagger\Security\ImplicitOauth2;
use Programster\Swagger\Security\InterfaceSecurityScheme;
use Programster\Swagger\Security\JwtBearer;
use Programster\Swagger\Security\NoSecurity;
use Programster\Swagger\Security\SecurityRequirement;
use Programster\Swagger\Security\SecurityRequirementCollection;

class Operation implements \JsonSerializable
{
    /**
     * https://swagger.io/specification/#operation-object
     * @param Method $method
     * @param ResponseCollection $responses
     * @param string|null $summary
     * @param string|null $description
     * @param ParameterCollection|null $parameters
     * @param RequestBody|Reference|null $requestBody
     * @param array|null $tags
     * @param string|null $operationId
     * @param string|null $externalDocs
     * @param Callbacks|null $callbacks
     * @param bool|null $deprecated
     * @param SecurityRequirementCollection|SecurityRequirement|null $security
     * @param ServerCollection|null $servers
     * @throws \Exception
     */
    public function __construct(
        private readonly Method $method,
        private readonly ResponseCollection $responses,
        private readonly ?string $summary = null,
        private readonly ?string $description = null,
        private readonly ?ParameterCollection $parameters = null,
        private readonly RequestBody | Reference | null $requestBody = null,
        private readonly ?array $tags = null,
        private readonly ?string $operationId = null,
        private readonly ?string $externalDocs = null,
        private readonly ?Callbacks $callbacks = null,
        private readonly ?bool $deprecated = null,
        private readonly SecurityRequirementCollection | SecurityRequirement | null $security = null,
        private readonly ?ServerCollection $servers = null
    )
    {
        if (count($responses->toArray()) == 0)
        {
            throw new \Exception("Paths need at least one response");
        }

        if ($this->tags !== null)
        {
            foreach ($tags as $tag)
            {
                if (is_string($tag) === false)
                {
                    throw new \Exception("Tag {$tag} needs to be a string");
                }
            }
        }
    }


    public function jsonSerialize() : mixed
    {
        $arrayForm = array(
            'responses' => $this->responses,
        );

        if ($this->summary !== null)  { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null)  { $arrayForm['description'] = $this->description; }
        if ($this->parameters !== null)  { $arrayForm['parameters'] = $this->parameters; }
        if ($this->requestBody !== null)  { $arrayForm['requestBody'] = $this->requestBody; }
        if ($this->tags !== null)  { $arrayForm['tags'] = $this->tags; }
        if ($this->operationId !== null)  { $arrayForm['operationId'] = $this->operationId; }
        if ($this->externalDocs !== null)  { $arrayForm['externalDocs'] = $this->externalDocs; }
        if ($this->callbacks !== null)  { $arrayForm['callbacks'] = $this->callbacks; }
        if ($this->deprecated !== null)  { $arrayForm['deprecated'] = $this->deprecated; }
        if ($this->security !== null)  { $arrayForm['security'] = $this->security; }
        if ($this->servers !== null)  { $arrayForm['servers'] = $this->servers; }

        return $arrayForm;
    }


    public function getMethod() : Method
    {
        return $this->method;
    }
}
