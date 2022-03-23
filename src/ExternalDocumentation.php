<?php

namespace Programster\Swagger;


class ExternalDocumentation implements \JsonSerializable
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $description = null
    )
    {

    }

    public function jsonSerialize(): mixed
    {
        $arrayForm = array('url' => $this->url);

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }

        return $arrayForm;
    }
}
