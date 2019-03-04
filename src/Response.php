<?php

/* 
 * 
 */

namespace Programster\Swagger;

class Response implements \JsonSerializable
{
    private $m_code;
    private $m_description;
    private $m_schema = NULL; # name of object to return in schema.
    
    
    public function __construct($code, $description, Definition $responseObjectDefinition = null)
    {
        if (!in_array($code, $this->getAcceptableResponseCodes()))
        {
            throw new Exception("Invalid response code: " . $code);
        }
        
        if ($responseObjectDefinition !== null)
        {
            $this->m_schema = array('$ref' => '#/definitions/' . $responseObjectDefinition->getName());
        }
        
        $this->m_code = $code;
        $this->m_description = $description;
    }
    
    
    public function getAcceptableResponseCodes()
    {
        return array(
            100, 101, 102, 
            200, 201, 202, 203, 204, 205, 206, 207, 
            300, 301, 302, 303, 304, 305, 306, 307, 
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 
            413, 414, 415, 416, 417, 418, 422, 423, 424, 425, 426, 449, 450, 
            500, 501, 502, 503, 504, 505, 
            506, 507, 509, 510,
        );
    }
    
    
    public function jsonSerialize()
    {
        $arrayForm = array(
            'description' => $this->m_description
        );
        
        if ($this->m_schema !== null) { $arrayForm['schema'] = $this->m_schema; }
        
        return $arrayForm;
    }
    
    
    public function get_code() { return $this->m_code; }

}
