<?php

namespace Programster\Swagger;


class ContentMap implements \JsonSerializable
{
    private array $m_map;


    public function __construct(MimeType $key, MediaType $mediaType)
    {
        $this->m_map = [$key->value => $mediaType];
    }


    /**
     * Add the media type for another mime type.
     * @param string $key - the mime type, e.g. "application/json".
     * @param MediaType $mediaType - the media type to use for it.
     * @return void
     * @throws \Exception - if there's already a media type for that mime type.
     */
    public function add(string $key, MediaType $mediaType)
    {
        if (isset($this->m_map[$key]))
        {
            throw new \Exception("{$key} already taken in " . __CLASS__);
        }

        $this->m_map[$key] = $mediaType;
    }


    /**
     * This is the same as add, except that it won't throw an exception if the key is already taken.
     * @param string $key - the mime type, e.g. "application/json".
     * @param MediaType $mediaType - the media type to use for it.
     * @return void
     */
    public function addOrReplace(string $key, MediaType $mediaType)
    {
        $this->m_map[$key] = $mediaType;
    }


    public function toArray(): array
    {
        return $this->m_map;
    }


    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
