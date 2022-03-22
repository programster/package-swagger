<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class License implements \JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $url
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = ['name' => $this->name];

        if ($this->url !== null)
        {
            $arrayForm['url'] = $this->url;
        }

        return $arrayForm;
    }
}
