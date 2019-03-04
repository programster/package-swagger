<?php

/* 
 * A sort of enum for the location of a parameter in the request.
 * E.g. is the parameter in the query, header, or body of the request etc.
 */

namespace Programster\Swagger;

class ParameterLocation
{
    private $m_location;
    
    private function __construct(string $location)
    {
        $this->m_location = $location;
    }
    
    
    public static function createQuery() { return new ParameterLocation("query"); }
    public static function createHeader() { return new ParameterLocation("header"); }
    public static function createPath() { return new ParameterLocation("path"); }
    public static function createFormData() { return new ParameterLocation("formData"); }
    public static function createBody() { return new ParameterLocation("body"); }
    
    
    public function __toString() 
    {
        return $this->m_location;
    }
}
