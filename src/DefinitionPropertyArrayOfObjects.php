<?php

/* 
 * A property for an object/definition.
 */

namespace Programster\Swagger;

class DefinitionPropertyArrayOfObjects implements DefinitionPropertyInterface
{
    private $m_object;
    
    public function __construct(string $name, Definition $object)
    {
        $this->m_name = $name;
        $this->m_object = $object;
    }
    
    
    public function jsonSerialize() 
    {
        return array(
            'type' => 'array',
            'items' => array(
                '$ref' => "#/definitions/" . $this->m_object->getName()
            )
        );
    }
   
    
    # Accessors
    public function getName() : string { return $this->m_name; }
}

