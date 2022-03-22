<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Info implements \JsonSerializable
{
    public function __construct(
        private readonly string $title,
        private readonly string $version,
        private readonly ?string $description = null,
        private readonly ?string $termsOfService = null,
        private readonly ?Contact $contact = null,
        private readonly ?License $license = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [
            'title' => $this->title,
            'version' => $this->version,
        ];

        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->termsOfService !== null) { $arrayForm['termsOfService'] = $this->termsOfService; }
        if ($this->contact !== null) { $arrayForm['contact'] = $this->contact; }
        if ($this->license !== null) { $arrayForm['license'] = $this->license; }

        return $arrayForm;
    }
}
