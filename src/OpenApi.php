<?php

namespace Programster\Swagger;

use Programster\Swagger\Security\SecurityRequirementCollection;

class OpenApi implements \JsonSerializable
{
    private readonly Info $m_info;
    private readonly array $m_pathMap;
    private readonly ?ServerCollection $m_servers;
    private readonly ?Components $m_components;
    private readonly ?SecurityRequirementCollection $m_security;
    private readonly ?TagCollection $m_tags;
    private readonly ?ExternalDocumentation $m_externalDocs;


    /**
     * @throws \Exception - if two of the paths have the same relative path.
     */
    public function __construct(
        Info $info,
        PathCollection $paths,
        ?ServerCollection $servers = null,
        ?Components $components = null,
        ?SecurityRequirementCollection $security = null,
        ?TagCollection $tags = null,
        ?ExternalDocumentation $externalDocs = null
    )
    {
        // Check to make sure that every path in the path collection provided is unique.
        $pathMap = [];

        foreach ($paths as $path)
        {
            /* @var $path Path */
            if (isset($pathMap[$path->getRelativePath()]))
            {
                $msg = "You cannot have two paths with the same relative path: {$path->getRelativePath()}";
                throw new \Exception($msg);
            }

            $pathMap[$path->getRelativePath()] = $path;
        }

        $this->m_info = $info;
        $this->m_pathMap = $pathMap;
        $this->m_servers = $servers;
        $this->m_components = $components;
        $this->m_security = $security;
        $this->m_tags = $tags;
        $this->m_externalDocs = $externalDocs;
    }


    /**
     * Convert this document into a string form which is in JSON format.
     * @return string
     * @throws \JsonException - if the document can't be encoded as JSON.
     */
    public function __toString() : string
    {
        return $this->toJson();
    }


    /**
     * @throws \JsonException - if the document can't be encoded as JSON.
     */
    public function toYaml() : string
    {
        $arrayForm = json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        return yaml_emit($arrayForm);
    }


    /**
     * @throws \JsonException - if the document can't be encoded as JSON.
     */
    public function toJson() : string
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }


    public function toArray()
    {
        $requiredFields = [
            'openapi' => "3.0.3",
            'info' => $this->m_info,
            'paths' => $this->m_pathMap,
        ];

        $optionalFields = [
            'servers' => $this->m_servers,
            'components' => $this->m_components,
            'security' => $this->m_security,
            'tags' => $this->m_tags,
            'externalDocs' => $this->m_externalDocs,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge($requiredFields, array_filter($optionalFields, $isProvided));
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
