<?php

/* 
 * 
 */

namespace Programster\Swagger;

class Document
{
    private $m_title;
    private $m_description;
    private $m_host;
    private $m_schemes;
    private $m_basePath;
    private $m_version;
    private $m_paths;
    private $m_definitions;
    private $m_swaggerVersion;
    private $m_document_version;
    private $m_security;
    private $m_securitySchemes;
    
    
    /**
     * Create your swagger document.
     * @param string $title - the Title of your API
     * @param string $description - a description for your API
     * @param string $host - Where your API is located (and the "try it out" buttons will send requests). It is a good
     *                       idea to have this pointing at a sandbox.
     * @param string $documentVersion - e.g. "1.0.0"
     * @param \Programster\Swagger\PathCollection $paths - a collection of all your P
     * @param \Programster\Swagger\DefinitionCollection $definitions
     * @param \Programster\Swagger\SecuritySchemeCollection $securitySchemes - collection of all the security schemes
     *                                                                         (this can be empty if you have no security)
     * @param array $security - optionally specify something like ["userSecurity" => array()] to apply the securityScheme
     *                          that was called "userSecurity" to the whole API.
     * @param array $schemes - should be an array of ["https"], or ["https", "http"]
     * @param string $basePath - the base path of your api. E.g. "/" or "/api" etc.
     */
    public function __construct(
        string $title, 
        string $description, 
        string $host, 
        string $documentVersion, 
        PathCollection $paths,
        DefinitionCollection $definitions,
        SecuritySchemeCollection $securitySchemes,
        array $security = array(),
        array $schemes = array("https"), 
        string $basePath = "/"
    )
    {
        $this->m_title = $title;
        $this->m_description = $description;
        $this->m_host = $host;
        $this->m_document_version = $documentVersion;
        $this->m_schemes = $schemes;
        $this->m_basePath = $basePath;;
        $this->m_swaggerVersion = "2.0";
        $this->m_definitions = array();
        $this->m_paths = array();
        $this->m_securitySchemes = $securitySchemes;
        $this->m_security = $security;
        
        foreach ($paths as $path)
        {
            /* @var $path Path */
            $this->m_paths[$path->getRoute()] = $path;
        }
        
        foreach ($definitions as $definition)
        {
            $this->m_definitions[$definition->getName()] = $definition;
        }
    }
    
    
    /**
     * Convert this document into a string form with is in JSON format.
     * @return string
     */
    public function __toString()
    {
        $document = new \stdClass();
        $document->swagger = $this->m_swaggerVersion;
        
        $document->info = new \stdClass();
        $document->info->title = $this->m_title;
        $document->info->description = $this->m_description;
        $document->info->version = $this->m_document_version;
        
        $document->host = $this->m_host;
        $document->schemes = $this->m_schemes;
        $document->basePath = $this->m_basePath;
        $document->consumes = array("multipart/form-data");
        $document->produces = array("application/json");
        
        if (count($this->m_securitySchemes) > 0)
        {
            $schemesArray = array();
            
            foreach ($this->m_securitySchemes as $scheme)
            {
                /* @var $scheme SecurityScheme */
                $schemesArray[$scheme->getSchemeName()] = $scheme->jsonSerialize();
            }
            
            $document->securityDefinitions = $schemesArray;
            $document->security = array($this->m_security);
        }
        
        # Paths is required, even if empty
        if (count($this->m_paths) > 0)
        {
            $document->paths = $this->m_paths;
        }
        else
        {
            $document->paths = new \stdClass();
        }
        
        if (count($this->m_definitions) > 0)
        {
            $document->definitions = $this->m_definitions;
        }
        
        return json_encode($document, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}