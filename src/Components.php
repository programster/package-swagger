<?php

namespace Programster\Swagger;

use JetBrains\PhpStorm\Internal\TentativeType;

class Components implements \JsonSerializable
{
    public function __construct(
        private readonly ?SchemaMap $schemas = null,
        private readonly ?ResponseMap $responses = null,
        private readonly ?ParameterMap $parameters = null,
        private readonly ?ExampleMap $examples = null,
        private readonly ?RequestBodyMap $requestBodies = null,
        private readonly ?HeaderMap $headers = null,
        private readonly ?SecuritySchemeMap $securitySchemes = null,
        private readonly ?LinkMap $links = null,
        private readonly ?CallbackMap $callbacks = null,
    )
    {

    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [];

        if ($this->schemas !== null) { $arrayForm['schemas'] = $this->schemas; }
        if ($this->responses !== null) { $arrayForm['responses'] = $this->responses; }
        if ($this->parameters !== null) { $arrayForm['parameters'] = $this->parameters; }
        if ($this->examples !== null) { $arrayForm['examples'] = $this->examples; }
        if ($this->requestBodies !== null) { $arrayForm['requestBodies'] = $this->requestBodies; }
        if ($this->headers !== null) { $arrayForm['headers'] = $this->headers; }
        if ($this->securitySchemes !== null) { $arrayForm['securitySchemes'] = $this->securitySchemes; }
        if ($this->links !== null) { $arrayForm['links'] = $this->links; }
        if ($this->callbacks !== null) { $arrayForm['callbacks'] = $this->callbacks; }

        return $arrayForm;
    }
}
