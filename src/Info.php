<?php

namespace Programster\Swagger;


class Info implements \JsonSerializable
{
    private readonly string $m_title;
    private readonly string $m_version;
    private readonly ?string $m_description;
    private readonly ?string $m_termsOfService;
    private readonly ?Contact $m_contact;
    private readonly ?License $m_license;


    public function __construct(
        string $title,
        string $version,
        ?string $description = null,
        ?string $termsOfService = null,
        ?Contact $contact = null,
        ?License $license = null,
    )
    {
        $this->m_title = $title;
        $this->m_version = $version;
        $this->m_description = $description;
        $this->m_termsOfService = $termsOfService;
        $this->m_contact = $contact;
        $this->m_license = $license;
    }


    public function jsonSerialize(): mixed
    {
        $requiredFields = [
            'title' => $this->m_title,
            'version' => $this->m_version,
        ];

        $optionalFields = [
            'description' => $this->m_description,
            'termsOfService' => $this->m_termsOfService,
            'contact' => $this->m_contact,
            'license' => $this->m_license,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_merge($requiredFields, array_filter($optionalFields, $isProvided));
    }
}
