<?php

namespace Programster\Swagger;


class Contact implements \JsonSerializable
{
    public function __construct(
        private readonly ?string $name = null,
        private readonly ?string $url = null,
        private readonly ?string $email = null,
    )
    {
        if ($name === null && $url === null && $email === null)
        {
            throw new \Exception("Contact object must contain at least one of name, url, or email.");
        }
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [];

        if ($this->name !== null) { $arrayForm['name'] = $this->name; }
        if ($this->url !== null) { $arrayForm['url'] = $this->url; }
        if ($this->email !== null) { $arrayForm['email'] = $this->email; }

        return $arrayForm;
    }
}
