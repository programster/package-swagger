<?php

/*
 * https://swagger.io/specification/#path-item-object
 */

namespace Programster\Swagger;


class Path implements \JsonSerializable
{
    private readonly array $operations;

    public function __construct(
        private readonly string $relativePath,
        private readonly ?string $ref = null,
        private readonly ?string $summary = null,
        private readonly ?string $description = null,
        private readonly ?ServerCollection $servers = null,
        private readonly ?ParameterCollection $parameters = null,
        Operation ...$operations
    )
    {
        $methods = [];

        // make sure two operations don't clash on the same method.
        foreach ($operations as $operation)
        {
            if (isset($methods[$operation->getMethod()->value]))
            {
                throw new \Exception("Duplicate path method: " . $operation->getMethod()->value);
            }

            /* @var $operation Operation */
            $methods[$operation->getMethod()->value] = $operation;
        }

        $this->operations = $operations;
    }


    public function toArray() : array
    {
        $arrayForm = [];

        if ($this->ref !== null) { $arrayForm['#ref'] = $this->ref; }
        if ($this->summary !== null) { $arrayForm['summary'] = $this->summary; }
        if ($this->description !== null) { $arrayForm['description'] = $this->description; }
        if ($this->servers !== null) { $arrayForm['servers'] = $this->servers; }
        if ($this->parameters !== null) { $arrayForm['parameters'] = $this->parameters; }

        foreach ($this->operations as $operation)
        {
            /* @var $operation \Programster\Swagger\Operation */
            $arrayForm[$operation->getMethod()->value] = $operation;
        }

        return $arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    public function getRelativePath() : string
    {
        return $this->relativePath;
    }
}
