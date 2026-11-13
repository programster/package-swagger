<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidDocumentException;
use Programster\Swagger\Exceptions\InvalidValueException;


/**
 * A path of the API, e.g. "/users/{id}", and the operations on it.
 * https://swagger.io/specification/v3/#paths-object
 */
final readonly class Path
{
    private string $m_relativePath;
    private PathItemInterface $m_item;


    /**
     * Create a path. Every variable in the path (e.g. the "id" in "/users/{id}") must have a parameter of the same
     * name in the path, for every operation, either in the item's parameters or the operation's own, and there can't
     * be a path parameter that isn't in the path. This can't be checked when the item is a reference.
     *
     * @param string $relativePath - the path, relative to the server's URL, e.g. "/users/{id}". It must start with a
     * "/".
     *
     * @param PathItemInterface $item - the operations on the path, or a reference to them in another document.
     *
     * @throws InvalidValueException - if the path doesn't start with a "/".
     * @throws InvalidDocumentException - if the path's variables don't match the operations' path parameters.
     */
    public function __construct(string $relativePath, PathItemInterface $item)
    {
        if (!str_starts_with($relativePath, "/"))
        {
            throw new InvalidValueException("A path must start with a \"/\", but \"{$relativePath}\" doesn't.");
        }

        if ($item instanceof PathItem)
        {
            self::assertPathParametersMatch($relativePath, $item);
        }

        $this->m_relativePath = $relativePath;
        $this->m_item = $item;
    }


    /**
     * Check that every variable in the path has a path parameter, for every operation, and that every path parameter
     * is in the path. A reference could be to a path parameter, so a variable without a parameter is only an error
     * when there are no references.
     *
     * @param string $relativePath - the path.
     *
     * @param PathItem $item - the operations on the path.
     *
     * @return void
     *
     * @throws InvalidDocumentException - if they don't match.
     */
    private static function assertPathParametersMatch(string $relativePath, PathItem $item) : void
    {
        preg_match_all('/\{([^}]*)\}/', $relativePath, $matches);
        $variables = array_unique($matches[1]);
        $sharedParameters = $item->getParameters();

        foreach ($item->getOperations() as $operation)
        {
            /** @var Operation $operation */
            $operationName = strtoupper($operation->getMethod()->value) . " {$relativePath}";
            $ownParameters = $operation->getParameters();

            $parameterNames = array_unique(array_merge(
                $sharedParameters->getPathParameterNames(),
                $ownParameters->getPathParameterNames()
            ));

            $mightHaveMore = $sharedParameters->hasReferences() || $ownParameters->hasReferences();

            foreach ($variables as $variable)
            {
                if (!$mightHaveMore && !in_array($variable, $parameterNames, true))
                {
                    $message = "{$operationName} has no path parameter named \"{$variable}\". Create one with "
                        . "ParameterLocation::PATH, for the path or the operation.";

                    throw new InvalidDocumentException($message);
                }
            }

            foreach ($parameterNames as $parameterName)
            {
                if (!in_array($parameterName, $variables, true))
                {
                    $message = "{$operationName} has a path parameter named \"{$parameterName}\", but the path has no "
                        . "{{$parameterName}} in it.";

                    throw new InvalidDocumentException($message);
                }
            }
        }
    }


    # Accessors
    public function getRelativePath() : string { return $this->m_relativePath; }
    public function getItem() : PathItemInterface { return $this->m_item; }
}
