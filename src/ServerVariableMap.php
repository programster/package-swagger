<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class ServerVariableMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $variableName, ServerVariable $serverVariable)
    {
        $this->m_map[$variableName] = $serverVariable;
    }


    public function add(string $variableName, ServerVariable $serverVariable)
    {
        if (isset($this->m_map[$variableName]))
        {
            throw new \Exception("{$variableName} already taken in " . __CLASS__);
        }

        $this->m_map[$variableName] = $serverVariable;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key
     * @param Header|Reference $serverVariable
     * @return void
     */
    public function addOrReplace(string $key, ServerVariable $serverVariable)
    {
        $this->m_map[$key] = $serverVariable;
    }


    public function jsonSerialize(): mixed
    {
        return $this->m_map;
    }
}
