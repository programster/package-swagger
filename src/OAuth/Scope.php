<?php

namespace Programster\Swagger\OAuth;

class Scope
{
    public function __construct(private readonly string $name, private readonly string $description)
    {
        // nothing.
    }

    # Accessors
    public function getName() : string { return $this->getName(); }
    public function getDescription() : string { return $this->getDescription(); }
}
