<?php

namespace Programster\Swagger;

use Programster\Swagger\Examples\ExampleMap;
use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;
use Programster\Swagger\Schemas\SchemaMap;
use Programster\Swagger\Security\SecuritySchemeMap;


/**
 * Objects that are defined once, and can then be referenced from anywhere in the document, such as schemas. Security
 * schemes are only defined here, and security requirements use them by name.
 * https://swagger.io/specification/v3/#components-object
 */
final readonly class Components implements \JsonSerializable
{
    private SchemaMap $m_schemas;
    private ResponseMap $m_responses;
    private ParameterMap $m_parameters;
    private ExampleMap $m_examples;
    private RequestBodyMap $m_requestBodies;
    private HeaderMap $m_headers;
    private SecuritySchemeMap $m_securitySchemes;
    private LinkMap $m_links;
    private CallbackMap $m_callbacks;
    private ExtensionMap $m_extensions;


    /**
     * @param SchemaMap $schemas - the schemas, which SchemaReferences can point at.
     *
     * @param ResponseMap $responses - the responses, which ResponseReferences can point at.
     *
     * @param ParameterMap $parameters - the parameters, which ParameterReferences can point at.
     *
     * @param ExampleMap $examples - the examples, which ExampleReferences can point at. Their names can only contain
     * letters, numbers, dots, dashes and underscores.
     *
     * @param RequestBodyMap $requestBodies - the request bodies, which RequestBodyReferences can point at.
     *
     * @param HeaderMap $headers - the headers, which HeaderReferences can point at.
     *
     * @param SecuritySchemeMap $securitySchemes - the security schemes, which security requirements use by name.
     *
     * @param LinkMap $links - the links, which LinkReferences can point at.
     *
     * @param CallbackMap $callbacks - the callbacks, which CallbackReferences can point at.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the components.
     *
     * @throws InvalidValueException - if an example's name isn't a valid component name.
     */
    public function __construct(
        SchemaMap $schemas = new SchemaMap(),
        ResponseMap $responses = new ResponseMap(),
        ParameterMap $parameters = new ParameterMap(),
        ExampleMap $examples = new ExampleMap(),
        RequestBodyMap $requestBodies = new RequestBodyMap(),
        HeaderMap $headers = new HeaderMap(),
        SecuritySchemeMap $securitySchemes = new SecuritySchemeMap(),
        LinkMap $links = new LinkMap(),
        CallbackMap $callbacks = new CallbackMap(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        // The other components' names are checked when they are named, as they can only go in the components.
        // Examples can also go elsewhere, where their names aren't limited.
        foreach ($examples->getKeys() as $name)
        {
            Validator::assertComponentName($name, "The example's name");
        }

        $this->m_schemas = clone $schemas;
        $this->m_responses = clone $responses;
        $this->m_parameters = clone $parameters;
        $this->m_examples = clone $examples;
        $this->m_requestBodies = clone $requestBodies;
        $this->m_headers = clone $headers;
        $this->m_securitySchemes = clone $securitySchemes;
        $this->m_links = clone $links;
        $this->m_callbacks = clone $callbacks;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Check whether there are no components (or extensions) at all, in which case the document leaves them out.
     * @return bool
     */
    public function isEmpty() : bool
    {
        $maps = [
            $this->m_schemas,
            $this->m_responses,
            $this->m_parameters,
            $this->m_examples,
            $this->m_requestBodies,
            $this->m_headers,
            $this->m_securitySchemes,
            $this->m_links,
            $this->m_callbacks,
            $this->m_extensions,
        ];

        $isEmpty = true;

        foreach ($maps as $map)
        {
            if (!$map->isEmpty())
            {
                $isEmpty = false;
            }
        }

        return $isEmpty;
    }


    public function jsonSerialize() : \stdClass
    {
        $maps = [
            'schemas' => $this->m_schemas,
            'responses' => $this->m_responses,
            'parameters' => $this->m_parameters,
            'examples' => $this->m_examples,
            'requestBodies' => $this->m_requestBodies,
            'headers' => $this->m_headers,
            'securitySchemes' => $this->m_securitySchemes,
            'links' => $this->m_links,
            'callbacks' => $this->m_callbacks,
        ];

        $fields = [];

        foreach ($maps as $name => $map)
        {
            $fields[$name] = $map->isEmpty() ? null : $map;
        }

        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getSecuritySchemes() : SecuritySchemeMap { return clone $this->m_securitySchemes; }
}
