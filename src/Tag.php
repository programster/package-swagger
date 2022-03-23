<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Tag implements \JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $description = null,
        private readonly ?ExternalDocumentation $externalDocs = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('name' => $this->name);

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->externalDocs !== null) { $arrayForm['externalDocs'] = $this->externalDocs; }

        return $arrayForm;
    }
}
