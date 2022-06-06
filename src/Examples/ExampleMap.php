<?php

namespace Programster\Swagger\Examples;


use Programster\Swagger\Header;
use Programster\Swagger\Reference;


class ExampleMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(NamedExample ...$examples)
    {
        foreach ($examples as $example)
        {
            $this->add($example);
        }
    }


    public function add(NamedExample $example) : void
    {
        if (isset($this->m_map[$example->getName()]))
        {
            throw new \Exception("{$name} already taken in " . __CLASS__);
        }

        $this->m_map[$example->getName()] = $example;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $name
     * @param Header|Reference $mediaType
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
            /* @var $namedExample \Programster\Swagger\Examples\NamedExample */
            $subExampleObject = $namedExample->getExample();

            if ($subExampleObject instanceof ValueExample)
            {
                $exampleArrayForm = [
                    'value' => $subExampleObject->jsonSerialize()
                ];
            }
            else
            {
                $exampleArrayForm = $subExampleObject->jsonSerialize();
            }

            if ($namedExample->getDescription() !== null)
            {
                $exampleArrayForm['description'] = $namedExample->getDescription();
            }

            if ($namedExample->getDescription() !== null)
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

