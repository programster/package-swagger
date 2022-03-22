<?php

/*
 * A map of named parameters. This is put within the components object, and allows parameters to be re-used
 * basedon their name in this map.
 * https://swagger.io/specification/#responses-object
 */

namespace Programster\Swagger;



class ParameterMap implements \JsonSerializable
{
    private readonly array $map;


    public function __construct(NamedParameter ...$namedParameters)
    {
        $arrayForm = [];

        foreach ($namedParameters as $namedParameter)
        {
            /* @var $namedParameter NamedParameter */
            if (isset($arrayForm[$namedParameter->getName()]))
            {
                throw new \Exception("Parameter name clash: " . $namedParameter->getName());
            }

            $arrayForm[$namedParameter->getName()] = $namedParameter->getParameter();
        }

        $this->map = $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->map;
    }
}
