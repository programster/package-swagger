<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\ResponseReference;


/**
 * A response with a name, to put in the components (in a ResponseMap).
 */
final readonly class NamedResponse
{
    private string $m_name;
    private ResponseInterface $m_response;


    /**
     * @param string $name - the name of the response, e.g. "NotFound". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param ResponseInterface $response - the response, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, ResponseInterface $response)
    {
        Validator::assertComponentName($name, "The response's name");
        $this->m_name = $name;
        $this->m_response = $response;
    }


    /**
     * Create a reference to this response, for using it elsewhere, once it is in the components.
     * @return ResponseReference
     */
    public function createReference() : ResponseReference
    {
        return ResponseReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getResponse() : ResponseInterface { return $this->m_response; }
}
