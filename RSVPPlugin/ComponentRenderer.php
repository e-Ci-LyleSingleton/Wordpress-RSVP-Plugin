<?php
namespace RSVPPlugin;


abstract class ComponentRenderer
{
    
    protected $config;

    function __construct($pluginConfiguration)
    {
        $this->config = $pluginConfiguration;
        
        add_action('wp_head', [
            $this,
            'OnRenderPageHead'
        ]);
        
        add_action('init', [
            $this,
            'OnPageActivate'
        ]);
    }

    abstract public function RenderComponentContent($context);

    public function OnRenderPageHead()
    {
        return "";
    }

    public function OnPageActivate()
    {
        return;
    }
}