<?php

/*
 * https://swagger.io/specification/#path-item-object
 */

namespace Programster\Swagger;

class PathItem
{
    public function __construct(
        private readonly ?string $ref,
        private readonly ?string $summary,
        private readonly ?string $description,
        private readonly ?Operation $get,
        private readonly ?Operation $put,
        private readonly ?Operation $post,
        private readonly ?Operation $delete,
        private readonly ?Operation $options,
        private readonly ?Operation $head,
        private readonly ?Operation $patch,
        private readonly ?Operation $trace,
        private readonly ?ServerCollection $servers,
        private readonly ?ParameterCollection $parameters,
    )
    {

    }
}
