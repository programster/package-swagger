<?php

namespace Programster\Swagger\OAuth;


class ScopeMap implements \JsonSerializable
{
    private readonly array $m_map;


    /**
     * @param Scope ...$scopes - the scopes, each of which must have a unique name.
     * @throws \Exception - if two of the scopes have the same name.
     */
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
        return $this->m_map;
    }
}
