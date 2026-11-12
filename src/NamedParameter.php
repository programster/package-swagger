<?php

/*
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;


class NamedParameter
{
    private readonly string $m_name;
    private readonly Parameter|Reference $m_parameter;


    /**
     * Create a uniquely named parameter. Every named parameter needs to have a unique name. E.g. if you have a
     * "first_name" parameter for both POST /users and POST /clients, then you may need to set names like
     * "users_first_name" and "clients_first_name" respectively.
     *
     * @param string $name - a name to remember the parameter by. This is not the parameter name that gets sent in a
     * request, but a UNIQUE name to remember this parameter by in this document, so that it may be re-used through
     * referencing elsewhere.
     *
     * @param Parameter|Reference $parameter - the parameter that is being uniquely named.
     */
    public function __construct(string $name, Parameter|Reference $parameter)
    {
        $this->m_name = $name;
        $this->m_parameter = $parameter;
    }


    # Accessors
    public function getName() : string { return $this->m_name; }
    public function getParameter() : Parameter|Reference { return $this->m_parameter; }
}
