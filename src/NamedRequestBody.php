<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\RequestBodyReference;


/**
 * A request body with a name, to put in the components (in a RequestBodyMap).
 */
final readonly class NamedRequestBody
{
    private string $m_name;
    private RequestBodyInterface $m_requestBody;


    /**
     * @param string $name - the name of the request body, e.g. "User". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param RequestBodyInterface $requestBody - the request body, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, RequestBodyInterface $requestBody)
    {
        Validator::assertComponentName($name, "The request body's name");
        $this->m_name = $name;
        $this->m_requestBody = $requestBody;
    }


    /**
     * Create a reference to this request body, for using it elsewhere, once it is in the components.
     * @return RequestBodyReference
     */
    public function createReference() : RequestBodyReference
    {
        return RequestBodyReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getRequestBody() : RequestBodyInterface { return $this->m_requestBody; }
}
