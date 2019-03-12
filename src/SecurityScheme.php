<?php

/* 
 * A security object for the swagger doc.
 * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#security-scheme-object
 */

namespace Programster\Swagger;

final class SecurityScheme implements \JsonSerializable
{
    private $m_schemeName = "";
    private $m_type;
    private $m_description;
    private $m_name;
    private $m_in;
    private $m_flow;
    private $m_authorizationUrl;
    private $m_tokenUrl;
    private $m_scopes;
    
    
    private function __construct(){}
    
    
    /**
     * Create a security object for an API that uses basic http authentication (e.g. .htaccess file).
     * @param string $schemeName - the name for the scheme, such as "userSecurity"
     * @return \Programster\Swagger\SecurityScheme
     */
    public static function createBasicAuth(string $schemeName) : SecurityScheme
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "basic";
        return $security;
    }
    
    
    /**
     * Create a security scheme for an API that uses an api key in the header.
     * @param string $schemeName
     * @return \Programster\Swagger\SecurityScheme
     */
    public static function createApiKey(string $schemeName) : SecurityScheme
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "apiKey";
        $security->m_in = "header";
        return $security;
    }
    
    
    /**
     * Create an OAuth 2 security scheme for the API.
     * @param string $schemeName - the name for this schemne, such as "UserSecurity"
     * @param string $authorizationUrl
     * @param array $scopes - an array of scope, like ["write:pets" => "modify pets in your account"]
     * @return \Programster\Swagger\SecurityScheme
     */
    public static function createOAuth2(string $schemeName, string $authorizationUrl, array $scopes) : SecurityScheme
    {
        $security = new SecurityScheme();
        $security->m_schemeName = $schemeName;
        $security->m_type = "oauth2";
        $security->m_authorizationUrl = $authorizationUrl;
        $security->m_flow = "implicit";
        $security->m_in = "header";
        $security->m_scopes = $scopes;
        
        return $security;
    }
    
    
    public function jsonSerialize() : array
    {
        if ($this->m_type !== null)
        {
            $arrayForm['type'] = $this->m_type;
        }
        
        if ($this->m_description !== null)
        {
            $arrayForm['description'] = $this->m_description;
        }
        
        if ($this->m_description !== null)
        {
            $arrayForm['in'] = $this->m_in;
        }
        
        if ($this->m_description !== null)
        {
            $arrayForm['flow'] = $this->m_flow;
        }
        
        if ($this->m_authorizationUrl !== null)
        {
            $arrayForm['authorizationUrl'] = $this->m_authorizationUrl;
        }
        
        if ($this->m_authorizationUrl !== null)
        {
            $arrayForm['tokenUrl'] = $this->m_tokenUrl;
        }
        
        if ($this->m_name !== null)
        {
            $arrayForm['name'] = $this->m_name;
        }
        
        if ($this->m_scopes !== null)
        {
            $arrayForm['scopes'] = $this->m_scopes;
        }
        
        return $arrayForm;
    }
    
    
    # Accessors
    public function getSchemeName() : string { return $this->m_schemeName; }
}

