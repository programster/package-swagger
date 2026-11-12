<?php

namespace Programster\Swagger;


class ServerVariableMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(string $variableName, ServerVariable $serverVariable)
    {
        $this->m_map = [$variableName => $serverVariable];
    }


    /**
     * Add another server variable.
     * @param string $variableName - the name of the variable, as used in the server's URL.
     * @param ServerVariable $serverVariable - the variable.
     * @return void
     * @throws \Exception - if there's already a variable with that name.
     */
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
     * @param string $key - the name of the variable, as used in the server's URL.
     * @param ServerVariable $serverVariable - the variable.
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
