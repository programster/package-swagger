<?php

namespace Programster\Swagger;


class Contact implements \JsonSerializable
{
    private readonly ?string $m_name;
    private readonly ?string $m_url;
    private readonly ?string $m_email;


    /**
     * @throws \Exception - if none of name, url or email are provided.
     */
    public function __construct(?string $name = null, ?string $url = null, ?string $email = null)
    {
        if ($name === null && $url === null && $email === null)
        {
            throw new \Exception("Contact object must contain at least one of name, url, or email.");
        }

        $this->m_name = $name;
        $this->m_url = $url;
        $this->m_email = $email;
    }


    public function jsonSerialize(): mixed
    {
        $optionalFields = [
            'name' => $this->m_name,
            'url' => $this->m_url,
            'email' => $this->m_email,
        ];

        $isProvided = function (mixed $value) : bool { return $value !== null; };
        return array_filter($optionalFields, $isProvided);
    }
}
