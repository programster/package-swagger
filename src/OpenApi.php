<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidDocumentException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Security\SecurityRequirementCollection;


/**
 * An OpenAPI 3.0.3 document, describing an API. Build it from the other objects, then output it with toJson() or
 * toYaml().
 * https://swagger.io/specification/v3/#openapi-object
 */
final readonly class OpenApi implements \JsonSerializable
{
    // The version of OpenAPI that the documents are in.
    public const OPENAPI_VERSION = "3.0.3";

    // Stands in for an empty object while the document is converted to YAML, as php-yaml would output it as [].
    private const EMPTY_OBJECT_PLACEHOLDER = "__programster_swagger_empty_object__";

    // Goes before a numeric key (e.g. a response code) while the document is converted to YAML, so that it can be
    // quoted, as OpenAPI requires, rather than php-yaml outputting it as a number.
    private const NUMERIC_KEY_PREFIX = "__programster_swagger_numeric_key__";

    private Info $m_info;
    private PathCollection $m_paths;
    private ServerCollection $m_servers;
    private Components $m_components;
    private ?SecurityRequirementCollection $m_security;
    private TagCollection $m_tags;
    private ?ExternalDocumentation $m_externalDocs;
    private ExtensionMap $m_extensions;


    /**
     * Create the document. As well as each object checking itself when it is created, this checks the things that
     * need the whole document: that every security requirement uses a security scheme that is in the components, and
     * that no two operations have the same operationId.
     *
     * @param Info $info - information about the API, such as its title and version.
     *
     * @param PathCollection $paths - the API's paths, and the operations on them.
     *
     * @param ServerCollection $servers - the servers the API is on. With none, it is on the server that the document
     * is served from.
     *
     * @param Components $components - the objects that are referenced from elsewhere, and the security schemes.
     *
     * @param SecurityRequirementCollection|null $security - the security for every operation that doesn't have its
     * own. Null (the default) means none.
     *
     * @param TagCollection $tags - descriptions of the tags that the operations are grouped under.
     *
     * @param ExternalDocumentation|null $externalDocs - documentation elsewhere about the API.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the document.
     *
     * @throws InvalidDocumentException - if a security requirement uses a scheme that isn't in the components, or two
     * operations have the same operationId.
     */
    public function __construct(
        Info $info,
        PathCollection $paths,
        ServerCollection $servers = new ServerCollection(),
        Components $components = new Components(),
        ?SecurityRequirementCollection $security = null,
        TagCollection $tags = new TagCollection(),
        ?ExternalDocumentation $externalDocs = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $schemeNames = $components->getSecuritySchemes()->getKeys();
        $operationIds = [];

        if ($security !== null)
        {
            self::assertSchemesExist($security, $schemeNames, "The document's security");
        }

        foreach ($paths as $path)
        {
            /** @var Path $path */
            $item = $path->getItem();

            // A reference's operations are in another document, so they can't be checked.
            $operations = ($item instanceof PathItem) ? $item->getOperations() : new OperationCollection();

            foreach ($operations as $operation)
            {
                /** @var Operation $operation */
                $operationName = strtoupper($operation->getMethod()->value) . " " . $path->getRelativePath();
                $operationSecurity = $operation->getSecurity();
                $operationId = $operation->getOperationId();

                if ($operationSecurity !== null)
                {
                    self::assertSchemesExist($operationSecurity, $schemeNames, "The security of {$operationName}");
                }

                if ($operationId !== null && isset($operationIds[$operationId]))
                {
                    $message = "{$operationName} and {$operationIds[$operationId]} both have the operationId "
                        . "\"{$operationId}\", but each operationId must be unique.";

                    throw new InvalidDocumentException($message);
                }

                if ($operationId !== null)
                {
                    $operationIds[$operationId] = $operationName;
                }
            }
        }

        $this->m_info = $info;
        $this->m_paths = clone $paths;
        $this->m_servers = clone $servers;
        $this->m_components = $components;
        $this->m_security = ($security === null) ? null : clone $security;
        $this->m_tags = clone $tags;
        $this->m_externalDocs = $externalDocs;
        $this->m_extensions = clone $extensions;
    }


    /**
     * Get the document as JSON, which is what toJson() returns.
     * @return string
     * @throws \JsonException - if the document can't be encoded as JSON.
     */
    public function __toString() : string
    {
        return $this->toJson();
    }


    /**
     * Get the document as pretty-printed JSON.
     * @return string
     * @throws \JsonException - if the document can't be encoded as JSON.
     */
    public function toJson() : string
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }


    /**
     * Get the document as YAML. This needs the yaml extension.
     * @return string
     * @throws \JsonException - if the document can't be encoded as JSON, which it is converted from.
     */
    public function toYaml() : string
    {
        $document = json_decode(json_encode($this, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $yaml = yaml_emit(self::convertForYaml($document), YAML_UTF8_ENCODING, YAML_LN_BREAK);
        $yaml = str_replace(self::EMPTY_OBJECT_PLACEHOLDER, "{}", $yaml);
        return preg_replace('/' . self::NUMERIC_KEY_PREFIX . '(\d+):/', '"$1":', $yaml);
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'openapi' => self::OPENAPI_VERSION,
            'info' => $this->m_info,
            'servers' => $this->m_servers->isEmpty() ? null : $this->m_servers,
            'paths' => $this->m_paths,
            'components' => $this->m_components->isEmpty() ? null : $this->m_components,
            'security' => $this->m_security,
            'tags' => $this->m_tags->isEmpty() ? null : $this->m_tags,
            'externalDocs' => $this->m_externalDocs,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }


    /**
     * @throws InvalidDocumentException - if one of the requirements uses a scheme that isn't in $schemeNames.
     */
    private static function assertSchemesExist(
        SecurityRequirementCollection $security,
        array $schemeNames,
        string $whose
    ) : void
    {
        foreach ($security->getSchemeNames() as $schemeName)
        {
            if (!in_array($schemeName, $schemeNames, true))
            {
                $message = "{$whose} uses the \"{$schemeName}\" security scheme, but the components don't have one "
                    . "with that name. Add it to the SecuritySchemeMap given to the Components.";

                throw new InvalidDocumentException($message);
            }
        }
    }


    /**
     * Convert the decoded JSON of the document into arrays for php-yaml, putting a placeholder in place of each empty
     * object, so that toYaml() can output it as {} rather than [], and before each numeric key of an object, so that
     * it can be quoted.
     *
     * @param mixed $value - part of the decoded document.
     *
     * @return mixed
     */
    private static function convertForYaml(mixed $value) : mixed
    {
        if ($value instanceof \stdClass && count(get_object_vars($value)) === 0)
        {
            $result = self::EMPTY_OBJECT_PLACEHOLDER;
        }
        elseif ($value instanceof \stdClass)
        {
            $result = [];

            foreach (get_object_vars($value) as $key => $item)
            {
                $yamlKey = is_int($key) ? self::NUMERIC_KEY_PREFIX . $key : $key;
                $result[$yamlKey] = self::convertForYaml($item);
            }
        }
        elseif (is_array($value))
        {
            $result = [];

            foreach ($value as $item)
            {
                $result[] = self::convertForYaml($item);
            }
        }
        else
        {
            $result = $value;
        }

        return $result;
    }
}
