<?php

namespace Programster\Swagger\Examples;


class NamedExample
{
    public function __construct(
        private readonly string $name,
        private readonly ExternalValueExample | ValueExample $example
    )
    {

    }


    # Accessors
    public function getName() : string { return $this->name; }
    public function getExample() : ExternalValueExample | ValueExample { return $this->example; }
}
