<?php

/*
 * https://swagger.io/specification/#request-body-object
 */

namespace Programster\Swagger;


class RequestBody implements \JsonSerializable
{
    public function __construct(
        private readonly ContentMap $content,
        private ?string $description = null,
        private ?bool $required = null
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = array('content' => $this->content);

        if ($this->required !== null) { $arrayForm ['required'] = $this->required; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }

        return $arrayForm;
    }
}
