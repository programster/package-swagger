<?php

namespace Programster\Swagger;

use Grpc\Call;

class NamedCallback
{
    public function __construct(
        private readonly string $name,
        private readonly Callback | Reference $callbackOrReference
    )
    {
    }

    public function getName() : string { return $this->name; }
    public function getCallback() : Callback | Reference { return $this->callbackOrReference; }
}
