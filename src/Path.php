<?php

/* 
 * Class for creating a path object in the swagger documentation.
 */

namespace Programster\Swagger;

class Path implements \JsonSerializable
{
    private $m_route;
    private $m_actions = array();
    
    
    public function __construct($route, PathAction ...$actions)
    {
        $this->m_route = $route;
        $this->m_actions = $actions;
    }
    
    
    public function jsonSerialize()
    {
        $arrayForm = array();
        
        foreach ($this->m_actions as $action)
        {
            /* @var $action PathAction */
            $methodString = (string)$action->getMethod();
            
            /* @var $action PathAction*/
            $arrayForm[$methodString] = $action;
        }
        
        return $arrayForm;
    }
    
    
    # Accessors
    public function getRoute() { return $this->m_route; }
}