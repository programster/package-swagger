<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\HeaderReference;


/**
 * A header with a name, to put in the components (in a HeaderMap), so that responses and encodings can reference it.
 * The name is only the component's name: the header is given its HTTP name where it is used, by a MappedHeader.
 */
final readonly class NamedHeader
{
    private string $m_name;
    private HeaderInterface $m_header;


    /**
     * @param string $name - the name of the component, e.g. "RateLimit". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param HeaderInterface $header - the header, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, HeaderInterface $header)
    {
        Validator::assertComponentName($name, "The header's name");
        $this->m_name = $name;
        $this->m_header = $header;
    }


    /**
     * Create a reference to this header, for using it elsewhere (with a MappedHeader), once it is in the components.
     * @return HeaderReference
     */
    public function createReference() : HeaderReference
    {
        return HeaderReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getHeader() : HeaderInterface { return $this->m_header; }
}
