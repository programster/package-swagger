<?php

/*
 * A property for an object/definition.
 */

namespace Programster\Swagger;

interface DefinitionPropertyInterface extends \JsonSerializable
{
    public function getName() : string;
    public function isRequired() : bool;
}

