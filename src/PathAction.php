<?php

/* 
 * 
 */

namespace Programster\Swagger;

class PathAction implements \JsonSerializable
{
    private $m_type;
    private $m_summary;
    private $m_description;
    private $m_parameters = array();
    private $m_responses;
    private $m_tags;
    
    
    public function __construct($type, $summary, $description, array $tags=array())
    {
        $acceptable_types = array("get", "post", "put", "patch", "delete");
        
        if (!in_array($type, $acceptable_types))
        {
            throw new Exception("Invalid type specified: " . $type);
        }
        
        $this->m_type = $type;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_tags = $tags;
    }
    
    
    public function addParameter(Parameter $parameter)
    {
        $this->m_parameters[] = $parameter;
    }
    
    public function addResponse(Response $response)
    {
        $this->m_responses[] = $response;
    }
    
    
    public function get_type(){ return $this->m_type; }


    public function jsonSerialize()
    {
        $arrayForm = array(
            'tags' => $this->m_tags,
            'summary' => $this->m_summary,
            'description' => $this->m_description,
            'parameters' => $this->m_parameters,
            'responses' => array()
        );
        
        foreach ($this->m_responses as $response)
        {
            /* @var $response Response */
            $arrayForm['responses'][$response->get_code()] = $response;
        }
        
        return $arrayForm;
    }

}