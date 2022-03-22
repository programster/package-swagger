<?php

/*
 * A security object for the swagger doc.
 * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#security-scheme-object
 */

namespace Programster\Swagger\SecuritySchemes;


final class JwtBearer implements \JsonSerializable
{
    private readonly string $m_schemeName;
    private readonly string $m_type;
    private readonly ?string $m_description;
    private readonly ?string $m_name;
    private readonly ?ParameterLocation $m_in;
    private readonly ?string $m_flow;
    private readonly ?string $m_authorizationUrl;
    private readonly ?string $m_tokenUrl;
    private readonly ?array $m_scopes;


    private function __construct(){}


    /**
     * Create a security object for an API that uses basic http authentication (e.g. .htaccess file).
     * @param string $schemeName - the name for the scheme, such as "userSecurity"
     * @return \Programster\Swagger\SecurityRequirement
     */
    public static function createBasicAuth(string $schemeName) : SecurityRequirement
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "basic";
        $security->m_description = null;
        $security->m_authorizationUrl = null;
        $security->m_in = null;
        $security->m_flow = null;
        $security->m_name = null;
        $security->m_scopes = null;
        return $security;
    }


    /**
     * Create a security scheme for an API that uses an api key in the header.
     * @param string $schemeName
     * @return \Programster\Swagger\SecurityRequirement
     */
    public static function createApiKey(string $schemeName) : SecurityRequirement
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "apiKey";
        $security->m_in = ParameterLocation::HEADER;
        $security->m_authorizationUrl = null;
        $security->m_flow = null;
        $security->m_name = null;
        $security->m_scopes = null;
        return $security;
    }


    /**
     * Create an OAuth 2 security scheme for the API.
     * @param string $schemeName - the name for this schemne, such as "UserSecurity"
     * @param string $authorizationUrl
     * @param array $scopes - an array of scope, like ["write:pets" => "modify pets in your account"]
     * @return \Programster\Swagger\SecurityRequirement
     */
    public static function createOAuth2(string $schemeName, string $authorizationUrl, array $scopes) : SecurityRequirement
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "oauth2";
        $security->m_authorizationUrl = $authorizationUrl;
        $security->m_flow = "implicit";
        $security->m_in = ParameterLocation::HEADER;
        $security->m_scopes = $scopes;
        $security->m_name = null;
        return $security;
    }


    public function jsonSerialize() : mixed
    {
        $arrayForm = [
            'type' => $this->m_type,

        ];

        if ($this->m_description !== null) { $arrayForm['description'] = $this->m_description; }
        if ($this->m_in !== null) { $arrayForm['in'] = $this->m_in; }
        if ($this->m_flow !== null) { $arrayForm['flow'] = $this->m_flow; }
        if ($this->m_authorizationUrl !== null) { $arrayForm['authorizationUrl'] = $this->m_authorizationUrl; }
        if ($this->m_authorizationUrl !== null) { $arrayForm['tokenUrl'] = $this->m_tokenUrl; }
        if ($this->m_name !== null) { $arrayForm['name'] = $this->m_name; }
        if ($this->m_scopes !== null) { $arrayForm['scopes'] = $this->m_scopes; }

        return $arrayForm;
    }


    # Accessors
    public function getSchemeName() : string { return $this->m_schemeName; }
}

