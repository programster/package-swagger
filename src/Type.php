<?php

/*
 * An "enum" for the type a parameter/property can be.
 */

namespace Programster\Swagger;

final class Type implements \JsonSerializable
{
    private $m_type;


    private function __construct(string $type)
    {
        $this->m_type = $type;
    }


    public static function createInt() { return new Type("integer"); }
    public static function createString() { return new Type("string"); }
    public static function createBool() { return new Type("boolean"); }
    public static function createNumber() { return new Type("number"); }


    public function jsonSerialize()
    {
        return $this->m_type;
    }
}
