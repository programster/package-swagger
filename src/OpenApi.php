<?php

/*
 *
 */

namespace Programster\Swagger;

use Programster\Swagger\Security\SecurityRequirementCollection;

class OpenApi implements \JsonSerializable
{
    private readonly array $pathMap;

    public function __construct(
        private readonly Info $info,
        PathCollection $paths,
        private readonly ?ServerCollection $servers = null,
        private readonly ?Components $components = null,
        private readonly ?SecurityRequirementCollection $security = null,
        private readonly ?TagCollection $tags = null,
        private readonly ?ExternalDocumentation $externalDocs = null
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

            /* @var $path Path */
            $pathMap[$path->getRelativePath()] = $path;
        }

        $this->pathMap = $pathMap;
    }


    /**
     * Convert this document into a string form with is in JSON format.
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }


    public function toYaml() : string
    {
        return yaml_emit(json_decode(json_encode($this), true));
    }


    public function toJson() : string
    {
        return json_encode($this, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES);
    }


    public function toArray()
    {
        $arrayForm = [
            'openapi' => "3.0.3",
            'info' => $this->info,
            'paths' => $this->pathMap,
        ];

        if ($this->servers !== null)  { $arrayForm['servers'] = $this->servers; }
        if ($this->components !== null)  { $arrayForm['components'] = $this->components; }
        if ($this->security !== null)  { $arrayForm['security'] = $this->security; }
        if ($this->tags !== null)  { $arrayForm['tags'] = $this->tags; }
        if ($this->externalDocs !== null)  { $arrayForm['externalDocs'] = $this->externalDocs; }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
