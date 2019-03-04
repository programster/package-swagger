<?php

/* 
 * An "enum" for the various web request methods.
 */

namespace Programster\Swagger;

class Method
{
    private $m_method;
    
    
    private function __construct(string $method)
    {
        $this->m_method = $method;
    }
    
    
    public static function createGet() { return new Method("get"); }
    public static function createPost() { return new Method("post"); }
    public static function createPut() { return new Method("put"); }
    public static function createPatch() { return new Method("patch"); }
    public static function createDelete() { return new Method("delete"); }
    
    public function __toString() { return $this->m_method; }
}