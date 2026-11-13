<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\LinkReference;


/**
 * A link with a name, to put in the components (in a LinkMap), or in a response's links.
 */
final readonly class NamedLink
{
    private string $m_name;
    private LinkInterface $m_link;


    /**
     * @param string $name - the name of the link, e.g. "UserGroup". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param LinkInterface $link - the link, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, LinkInterface $link)
    {
        Validator::assertComponentName($name, "The link's name");
        $this->m_name = $name;
        $this->m_link = $link;
    }


    /**
     * Create a reference to this link, for using it elsewhere, once it is in the components.
     * @return LinkReference
     */
    public function createReference() : LinkReference
    {
        return LinkReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getLink() : LinkInterface { return $this->m_link; }
}
