<?php

namespace Programster\Swagger\Examples;


class NamedExample
{
    private readonly string $m_name;
    private readonly ExternalValueExample|ValueExample $m_example;
    private readonly ?string $m_summary;
    private readonly ?string $m_description;


    /**
     * Create a named example.
     * @param string $name - the unique name for this example to distinguish it from other examples.
     * @param ExternalValueExample|ValueExample $example - the example itself.
     * @param string|null $summary - a summary of this example.
     * @param string|null $description - a longer description of this example.
     */
    public function __construct(
        string $name,
        ExternalValueExample|ValueExample $example,
        ?string $summary = null,
        ?string $description = null,
    )
    {
        $this->m_name = $name;
        $this->m_example = $example;
        $this->m_summary = $summary;
        $this->m_description = $description;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getExample() : ExternalValueExample|ValueExample { return $this->m_example; }
    public function getSummary() : ?string { return $this->m_summary; }
    public function getDescription() : ?string { return $this->m_description; }
}
