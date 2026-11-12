<?php

namespace Programster\Swagger;


class NamedCallback
{
    private readonly string $m_name;
    private readonly Callback|Reference $m_callbackOrReference;


    public function __construct(string $name, Callback|Reference $callbackOrReference)
    {
        $this->m_name = $name;
        $this->m_callbackOrReference = $callbackOrReference;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getCallback() : Callback|Reference { return $this->m_callbackOrReference; }
}
