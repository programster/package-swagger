<?php

namespace Programster\Swagger;

use Programster\Swagger\Exceptions\InvalidValueException;
use Programster\Swagger\Extensions\ExtensionMap;
use Programster\Swagger\Internal\JsonObject;
use Programster\Swagger\Internal\Validator;


/**
 * Who to contact about the API.
 * https://swagger.io/specification/v3/#contact-object
 */
final readonly class Contact implements \JsonSerializable
{
    private ?string $m_name;
    private ?string $m_url;
    private ?string $m_email;
    private ExtensionMap $m_extensions;


    /**
     * @param string|null $name - the name of the person or organisation.
     * @param string|null $url - the URL of their website.
     * @param string|null $email - their email address.
     * @param ExtensionMap $extensions - specification extensions ("x-" fields) to add to the contact.
     * @throws InvalidValueException - if none of them are given, the URL isn't a URL, or the email isn't an email.
     */
    public function __construct(
        ?string $name = null,
        ?string $url = null,
        ?string $email = null,
        ExtensionMap $extensions = new ExtensionMap()
    )
    {
        if ($name === null && $url === null && $email === null)
        {
            throw new InvalidValueException("A Contact needs at least one of a name, URL or email address.");
        }

        if ($url !== null)
        {
            Validator::assertUrl($url, "The contact's URL");
        }

        if ($email !== null)
        {
            Validator::assertEmail($email, "The contact's email");
        }

        $this->m_name = $name;
        $this->m_url = $url;
        $this->m_email = $email;
        $this->m_extensions = clone $extensions;
    }


    public function jsonSerialize() : \stdClass
    {
        $fields = [
            'name' => $this->m_name,
            'url' => $this->m_url,
            'email' => $this->m_email,
        ];

        return JsonObject::create($fields, $this->m_extensions);
    }
}
