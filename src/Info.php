<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * Information about the API, such as its title and version.
 * https://swagger.io/specification/v3/#info-object
 */
final readonly class Info implements \JsonSerializable
{
    private string $m_title;
    private string $m_version;
    private ?string $m_description;
    private ?string $m_termsOfService;
    private ?Contact $m_contact;
    private ?License $m_license;
    private ExtensionMap $m_extensions;


    /**
     * @param string $title - the title of the API.
     *
     * @param string $version - the version of the API (not of OpenAPI, or of this package), e.g. "1.2.0".
     *
     * @param string|null $description - a description of the API. CommonMark syntax may be used.
     *
     * @param string|null $termsOfService - the URL of the API's terms of service.
     *
     * @param Contact|null $contact - who to contact about the API.
     *
     * @param License|null $license - the license that the API is under.
     *
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the info.
     *
     * @throws InvalidValueException - if the title or version is empty, or termsOfService isn't a URL.
     */
    public function __construct(
        string $title,
        string $version,
        ?string $description = null,
        ?string $termsOfService = null,
        ?Contact $contact = null,
        ?License $license = null,
        ExtensionMap $extensions = new ExtensionMap(),
    )
    {
        Validator::assertNotEmpty($title, "The API's title");
        Validator::assertNotEmpty($version, "The API's version");

        if ($termsOfService !== null)
        {
            Validator::assertUrl($termsOfService, "The terms of service");
        }

        $this->m_title = $title;
        $this->m_version = $version;
        $this->m_description = $description;
        $this->m_termsOfService = $termsOfService;
        $this->m_contact = $contact;
        $this->m_license = $license;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'title' => $this->m_title,
            'description' => $this->m_description,
            'termsOfService' => $this->m_termsOfService,
            'contact' => $this->m_contact,
            'license' => $this->m_license,
            'version' => $this->m_version,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
