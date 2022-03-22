<?php

namespace Programster\Swagger\OAuth;


class OauthFlows implements JsonSerializable
{
    public function __construct(
        private readonly ?ImplicitFlow $implicit = null,
        private readonly ?PasswordFlow $password = null,
        private readonly ?ClientCredentialsFlow $clientCredentials = null,
        private readonly ?AuthorizationCodeFlow $auhtorizationCode = null,
    )
    {
        if ($implicit === null && $password === null && $clientCredentials === null && $auhtorizationCode === null)
        {
            throw new Exception("Must provide at least one of the types of flows.");
        }
    }


    public function jsonSerialize(): mixed
    {
        $arrayForm = [];

        if ($this->implicit !== null) { $arrayForm['clientCredentials'] = $this->implicit; }
        if ($this->password !== null) { $arrayForm['clientCredentials'] = $this->password; }
        if ($this->clientCredentials !== null) { $arrayForm['clientCredentials'] = $this->clientCredentials; }
        if ($this->auhtorizationCode !== null) { $arrayForm['clientCredentials'] = $this->auhtorizationCode; }

        return $arrayForm;
    }
}
