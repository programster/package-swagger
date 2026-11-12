<?php

/*
 * A map of named parameters. This is put within the components object, and allows parameters to be re-used
 * based on their name in this map.
 * https://swagger.io/specification/#components-object
 */

namespace Programster\Swagger;


class ParameterMap implements \JsonSerializable
{
    private readonly array $m_map;


    /**
     * @param NamedParameter ...$namedParameters - the parameters, each of which must have a unique name.
     * @throws \Exception - if two of the parameters have the same name.
     */
    public function __construct(NamedParameter ...$namedParameters)
    {
        $map = [];

        foreach ($namedParameters as $namedParameter)
        {
            if (isset($map[$namedParameter->getName()]))
            {
                throw new \Exception("Parameter name clash: " . $namedParameter->getName());
            }

            $map[$namedParameter->getName()] = $namedParameter->getParameter();
        }

        $this->m_map = $map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_map;
    }
}
