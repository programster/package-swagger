<?php

namespace Programster\Swagger\Examples;


class NamedExample
{
    public function __construct(
        private readonly string $name,
        private readonly ExternalValueExample | ValueExample $example,
        private readonly ?string $summary,
        private readonly ?string $description,
    )
    {

    }


    # Accessors
    public function getName() : string { return $this->name; }
    public function getExample() : ExternalValueExample | ValueExample { return $this->example; }
    public function getSummary() : ?string { return $this->name; }
    public function getDescription() : ?string { return $this->name; }
}
