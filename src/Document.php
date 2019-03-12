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
    private $m_produces;
    private $m_version;
    private $m_paths;
    private $m_definitions;
    private $m_swaggerVersion;
    private $m_document_version;
    private $m_security;
    
    public function __construct(
        string $title, 
        string $description, 
        string $host, 
        string $documentVersion, 
        PathCollection $paths,
        DefinitionCollection $definitions,
        SecurityScheme $security,
        $schemes = array("https"), 
        $basePath = "/", 
        $produces = array()
    )
    {
        $this->m_title = $title;
        $this->m_description = $description;
        $this->m_host = $host;
        $this->m_document_version = $documentVersion;
        $this->m_schemes = $schemes;
        $this->m_basePath = $basePath;
        $this->m_produces = $produces;
        $this->m_swaggerVersion = "2.0";
        $this->m_definitions = array();
        $this->m_paths = array();
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
        $document->schemes = array("https");
        $document->basePath = $this->m_basePath;
        $document->consumes = array("multipart/form-data");
        $document->produces = array("application/json");
        
        $securityArray = $this->m_security->jsonSerialize();
        
        if (count($securityArray) !== 0)
        {
            $document->securityDefinitions = array($this->m_security->getSchemeName() => $this->m_security);
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