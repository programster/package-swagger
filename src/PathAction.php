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
    private $m_parameters;
    private $m_responses;
    private $m_tags;
    
    
    public function __construct(
        string $type, 
        string $summary, 
        string $description, 
        ParameterCollection $parameters, 
        ResponseCollection $responses,
        array $tags = array()
    )
    {
        $acceptable_types = array("get", "post", "put", "patch", "delete");
        
        if (!in_array($type, $acceptable_types))
        {
            throw new Exception("Invalid type specified: " . $type);
        }
        
        if (count($responses) == 0)
        {
            throw new Exception("Paths need at least one response");
        }
        
        $this->m_type = $type;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_tags = $tags;
        $this->m_parameters = $parameters;
        $this->m_responses = $responses;
    }
    
    
    public function jsonSerialize()
    {
        $arrayForm = array(
            'tags' => $this->m_tags,
            'summary' => $this->m_summary,
            'description' => $this->m_description,
            'parameters' => $this->m_parameters->getArrayCopy(),
            'responses' => array()
        );
        
        foreach ($this->m_responses as $response)
        {
            /* @var $response Response */
            $arrayForm['responses'][$response->get_code()] = $response;
        }
        
        return $arrayForm;
    }
    
    
    public function getType(){ return $this->m_type; }
}