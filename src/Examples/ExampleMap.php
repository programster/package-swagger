<?php

namespace Programster\Swagger\Examples;


class ExampleMap implements \JsonSerializable
{
    private array $m_map = [];


    /**
     * @param NamedExample ...$examples - the examples, each of which must have a unique name.
     * @throws \Exception - if two of the examples have the same name.
     */
    public function __construct(NamedExample ...$examples)
    {
        foreach ($examples as $example)
        {
            $this->add($example);
        }
    }


    /**
     * Add another example.
     * @param NamedExample $example - the example to add.
     * @return void
     * @throws \Exception - if there's already an example with that name.
     */
    public function add(NamedExample $example) : void
    {
        if (isset($this->m_map[$example->getName()]))
        {
            throw new \Exception("{$example->getName()} already taken in " . __CLASS__);
        }

        $this->m_map[$example->getName()] = $example;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the name is already taken.
     * @param NamedExample $example - the example to add, or to replace the existing one with the same name.
     * @return void
     */
    public function addOrReplace(NamedExample $example)
    {
        $this->m_map[$example->getName()] = $example;
    }


    public function toArray(): array
    {
        $mapArrayForm = [];

        foreach ($this->m_map as $exampleName => $namedExample)
        {
            /* @var $namedExample NamedExample */
            $subExampleObject = $namedExample->getExample();

            if ($subExampleObject instanceof ValueExample)
            {
                $exampleArrayForm = ['value' => $subExampleObject->jsonSerialize()];
            }
            else
            {
                $exampleArrayForm = $subExampleObject->jsonSerialize();
            }

            if ($namedExample->getDescription() !== null)
            {
                $exampleArrayForm['description'] = $namedExample->getDescription();
            }

            if ($namedExample->getSummary() !== null)
            {
                $exampleArrayForm['summary'] = $namedExample->getSummary();
            }

            $mapArrayForm[$exampleName] = $exampleArrayForm;
        }

        return $mapArrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
