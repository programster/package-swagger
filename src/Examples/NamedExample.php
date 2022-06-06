<?php

namespace Programster\Swagger\Examples;


class NamedExample
{
    /**
     * Create a named example.
     * @param string $name - the unique name for this example to distinguish it from other examples.
     * @param ExternalValueExample|ValueExample $example
     * @param string|null $summary - a summary of this example.
     * @param string|null $description - a longer description of this example.
     */
    public function __construct(
        private readonly string $name,
        private readonly ExternalValueExample | ValueExample $example,
        private readonly ?string $summary = null,
        private readonly ?string $description = null,
    )
    {

    }


    # Accessors
    public function getName() : string { return $this->name; }
    public function getExample() : ExternalValueExample | ValueExample { return $this->example; }
    public function getSummary() : ?string { return $this->name; }
    public function getDescription() : ?string { return $this->name; }
}
