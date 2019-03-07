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
    
    
    private function __construct() {}
    
    
    /**
     * Create a response that doesn't return any object/array.
     * @param int $code - the HTML response code
     * @param string $description
     * @param \Programster\Swagger\Definition $definition
     * @return \Programster\Swagger\Response
     * @throws Exception
     */
    public static function createBasicResponse(int $code, string $description) : Response
    {
        $response = new Response();
        
        if (!in_array($code, Response::getAcceptableResponseCodes()))
        {
            throw new Exception("Invalid response code: " . $code);
        }
        
        $response->m_code = $code;
        $response->m_description = $description;
        return $response;
    }
    
    
    /**
     * Create a response that returns a single object
     * @param int $code - the HTML response code
     * @param string $description
     * @param \Programster\Swagger\Definition $definition
     * @return \Programster\Swagger\Response
     * @throws Exception
     */
    public static function createObjectResponse(int $code, string $description, Definition $definition) : Response
    {
        $response = new Response();
        
        if (!in_array($code, Response::getAcceptableResponseCodes()))
        {
            throw new Exception("Invalid response code: " . $code);
        }
        
        $response->m_schema = array('$ref' => '#/definitions/' . $definition->getName());
        $response->m_code = $code;
        $response->m_description = $description;
        return $response;
    }
    
    
    /**
     * Create a response that returns an array of objects.
     * @param int $code - the HTML response code
     * @param string $description
     * @param \Programster\Swagger\Definition $definition
     * @return \Programster\Swagger\Response
     * @throws Exception
     */
    public static function createArrayOfObjectsResponse(int $code, string $description, Definition $definition) : Response
    {
        $response = new Response();
        
        if (!in_array($code, Response::getAcceptableResponseCodes()))
        {
            throw new Exception("Invalid response code: " . $code);
        }
        
        $response->m_schema = array(
            "type" => "array",
            "items" => array(
                '$ref' => '#/definitions/' . $definition->getName()
            )
        );
        
        $response->m_code = $code;
        $response->m_description = $description;
        return $response;
    }
    
    
    private static function getAcceptableResponseCodes()
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
