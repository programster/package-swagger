<?php

/*
 *
 */

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class OpenApi implements \JsonSerializable
{

    public function __construct(
        private readonly Info $info,
        private readonly ServerCollection $servers,
        private readonly Paths $paths,
        private readonly Components $components,
        private readonly SecurityRequirementCollection $security,
        private readonly ?TagCollection $tags = null,
        private readonly ?ExternalDocumentation $externalDocs = null
    )
    {

    }


    /**
     * Convert this document into a string form with is in JSON format.
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [];

        return $arrayForm;
    }
}
