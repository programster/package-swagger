<?php

namespace Programster\Swagger\Internal;

use Programster\Swagger\Extensions\Extension;
use Programster\Swagger\Extensions\ExtensionMap;


/**
 * Builds the JSON objects that the package's objects serialize to.
 *
 * @internal - this is used by the package itself, and isn't part of its public API.
 */
final class JsonObject
{
    /**
     * Create the JSON object for some fields, leaving out the ones that are null, as OpenAPI treats a missing field
     * as unset. An object is always returned, even with no fields, so that it is output as {} rather than [].
     *
     * @param array $fields - the fields, keyed by their names in the JSON.
     *
     * @param ExtensionMap $extensions - the object's specification extensions, which are added after the fields.
     *
     * @return \stdClass
     */
    public static function create(array $fields, ExtensionMap $extensions = new ExtensionMap()) : \stdClass
    {
        $isProvided = function (mixed $value) : bool { return $value !== null; };
        $object = (object)array_filter($fields, $isProvided);

        foreach ($extensions as $extension)
        {
            /** @var Extension $extension */
            $object->{$extension->getName()} = $extension->getValue();
        }

        return $object;
    }
}
