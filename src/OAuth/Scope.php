<?php

namespace Programster\Swagger\OAuth;

class Scope
{
    private readonly string $m_name;
    private readonly string $m_description;


    public function __construct(string $name, string $description)
    {
        $this->m_name = $name;
        $this->m_description = $description;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getDescription() : string { return $this->m_description; }
}
