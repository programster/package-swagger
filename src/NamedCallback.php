<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\References\CallbackReference;


/**
 * A callback with a name, to put in the components (in a CallbackMap), or in an operation's callbacks.
 */
final readonly class NamedCallback
{
    private string $m_name;
    private CallbackInterface $m_callback;


    /**
     * @param string $name - the name of the callback, e.g. "onUserCreated". It can only contain letters, numbers, dots,
     * dashes and underscores.
     *
     * @param CallbackInterface $callback - the callback, or a reference to one.
     *
     * @throws InvalidValueException - if the name isn't a valid component name.
     */
    public function __construct(string $name, CallbackInterface $callback)
    {
        Validator::assertComponentName($name, "The callback's name");
        $this->m_name = $name;
        $this->m_callback = $callback;
    }


    /**
     * Create a reference to this callback, for using it elsewhere, once it is in the components.
     * @return CallbackReference
     */
    public function createReference() : CallbackReference
    {
        return CallbackReference::createToComponent($this->m_name);
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getCallback() : CallbackInterface { return $this->m_callback; }
}
