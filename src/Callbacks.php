<?php

namespace Programster\Swagger;


class Callbacks implements \JsonSerializable
{
    private readonly array $arrayForm;


    public function __construct(NamedCallback ...$callbacks)
    {
        $arrayForm = [];

        foreach ($callbacks as $namedCallback)
        {
            /* @var $namedCallback \Programster\Swagger\NamedCallback */

            if (isset($arrayForm[$namedCallback->getName()]))
            {
                throw new \Exception("You can't have two callbacks with the same name: {$namedCallback->getName()}");
            }

            $arrayForm[$namedCallback->getName()] = $namedCallback->getCallback();
        }

        $this->arrayForm = $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->arrayForm;
    }
}
