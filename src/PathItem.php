<?php

namespace Programster\Swagger;

use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;


/**
 * The operations on a path (or a callback's URL), and anything they share, such as their parameters. A path is
 * created from one of these with a Path, and a callback with a Callback.
 * https://swagger.io/specification/v3/#path-item-object
 */
final readonly class PathItem implements PathItemInterface
{
    private OperationCollection $m_operations;
    private ?string $m_summary;
    private ?string $m_description;
    private ServerCollection $m_servers;
    private ParameterCollection $m_parameters;
    private ExtensionMap $m_extensions;


    /**
     * @param OperationCollection $operations - the operations, e.g. GET and POST. There can only be one per method.
     * @param string|null $summary - a short summary of what the operations are for.
     * @param string|null $description - a longer description of what the operations are for.
     * @param ServerCollection $servers - the servers these operations are on, instead of the document's.
     * @param ParameterCollection $parameters - the parameters that all the operations take.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the path item.
     */
    public function __construct(
        OperationCollection $operations = new OperationCollection(),
        ?string $summary = null,
        ?string $description = null,
        ServerCollection $servers = new ServerCollection(),
        ParameterCollection $parameters = new ParameterCollection(),
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        $this->m_operations = clone $operations;
        $this->m_summary = $summary;
        $this->m_description = $description;
        $this->m_servers = clone $servers;
        $this->m_parameters = clone $parameters;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'summary' => $this->m_summary,
            'description' => $this->m_description,
        ];

        foreach ($this->m_operations as $operation)
        {
            /** @var Operation $operation */
            $fields[$operation->getMethod()->value] = $operation;
        }

        $fields['servers'] = $this->m_servers->isEmpty() ? null : $this->m_servers;
        $fields['parameters'] = $this->m_parameters->isEmpty() ? null : $this->m_parameters;
        return JsonObject::create($fields, $this->m_extensions);
    }


    # Accessors
    public function getOperations() : OperationCollection { return clone $this->m_operations; }
    public function getParameters() : ParameterCollection { return clone $this->m_parameters; }
}
