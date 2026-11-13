<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * A header under its HTTP name, to put in the HeaderCollection of a response or an encoding.
 */
final readonly class MappedHeader
{
    private string $m_name;
    private HeaderInterface $m_header;


    /**
     * @param string $name - the HTTP name of the header, e.g. "X-Rate-Limit".
     * @param HeaderInterface $header - the header, or a reference to one.
     * @throws InvalidValueException - if the name isn't a valid header name.
     */
    public function __construct(string $name, HeaderInterface $header)
    {
        Validator::assertHeaderName($name);
        $this->m_name = $name;
        $this->m_header = $header;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getHeader() : HeaderInterface { return $this->m_header; }
}
