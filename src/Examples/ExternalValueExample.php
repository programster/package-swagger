<?php

namespace Programster\Swagger\Examples;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Internal\Validator;


/**
 * An example that is somewhere else, at a URL, e.g. for an example that can't easily be written in JSON (such as an
 * image). OpenAPI only allows these in a map of named examples (an ExampleMap), not as a single example.
 * https://swagger.io/specification/v3/#example-object
 */
final readonly class ExternalValueExample
{
    private string $m_url;


    /**
     * @param string $url - the URL of the example.
     * @throws InvalidValueException - if $url isn't a URL.
     */
    public function __construct(string $url)
    {
        Validator::assertUrl($url, "The external example's URL");
        $this->m_url = $url;
    }


    # Accessors
    public function getUrl() : string { return $this->m_url; }
}
