<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * A request that the API makes back to the client, e.g. a webhook, at a URL given by a runtime expression.
 * https://swagger.io/specification/v3/#callback-object
 */
final readonly class Callback implements CallbackInterface
{
    private string $m_expression;
    private PathItemInterface $m_pathItem;
    private ExtensionMap $m_extensions;


    /**
     * @param string $expression - a runtime expression for the URL that the request is sent to, e.g.
     * '{$request.body#/callbackUrl}'.
     *
     * @param PathItemInterface $pathItem - the operations that may be called at that URL, or a reference to them in
     * another document.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the callback.
     *
     * @throws InvalidValueException - if the expression is empty.
     */
    public function __construct(
        string $expression,
        PathItemInterface $pathItem,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        Validator::assertNotEmpty($expression, "The callback's expression");
        $this->m_expression = $expression;
        $this->m_pathItem = $pathItem;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        return JsonObject::create([$this->m_expression => $this->m_pathItem], $this->m_extensions);
    }
}
