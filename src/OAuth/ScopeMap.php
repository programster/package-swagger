<?php

namespace Programster\Swagger\OAuth;

use JetBrains\PhpStorm\Internal\TentativeType;

class ScopeMap implements \JsonSerializable
{
    private readonly array $m_map;


    public function __construct(Scope ...$scopes)
    {
        $mapping = [];

        foreach ($scopes as $scope)
        {
            if (isset($mapping[$scope->getName()]))
            {
                throw new \Exception("Duplicate scope name: {$scope->getName()} when creating " . __CLASS__);
            }

            $mapping[$scope->getName()] = $scope->getDescription();
        }

        $this->m_map = $mapping;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
