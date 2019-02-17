<?php

/* 
 * Class for creating a path object in the swagger documentation.
 */

namespace Programster\Swagger;

class Path implements \JsonSerializable
{
    private $m_route;
    private $m_actions = array();
    
    
    public function __construct($route)
    {
        $this->m_route = $route;
    }
    
    
    public function addAction(PathAction $action)
    {
        $this->m_actions[$action->get_type()] = $action;
    }
    
    
    public function jsonSerialize()
    {
        $arrayForm = array();
        
        foreach ($this->m_actions as  $action)
        {
            /* @var $action PathAction*/
            $arrayForm[$action->get_type()] = $action;
        }
        
        return $arrayForm;
    }
    
    # Accessors
    public function get_route() { return $this->m_route; }
}