<?php

namespace Programster\Swagger;


class Callbacks implements \JsonSerializable
{
    private readonly array $m_arrayForm;


    /**
     * @param NamedCallback ...$callbacks - the callbacks, each of which must have a unique name.
     * @throws \Exception - if two of the callbacks have the same name.
     */
    public function __construct(NamedCallback ...$callbacks)
    {
        $arrayForm = [];

        foreach ($callbacks as $namedCallback)
        {
            if (isset($arrayForm[$namedCallback->getName()]))
            {
                throw new \Exception("You can't have two callbacks with the same name: {$namedCallback->getName()}");
            }

            $arrayForm[$namedCallback->getName()] = $namedCallback->getCallback();
        }

        $this->m_arrayForm = $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_arrayForm;
    }
}
