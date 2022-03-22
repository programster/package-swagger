<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Server implements \JsonSerializable
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $description,
        private readonly ?ServerVariableMap $variables
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = ['url' => $this->url];

        if ($this->description !== null) { $arrayForm = ['description' => $this->description]; }
        if ($this->variables !== null) { $arrayForm = ['variables' => $this->variables]; }

        return $arrayForm;
    }
}
