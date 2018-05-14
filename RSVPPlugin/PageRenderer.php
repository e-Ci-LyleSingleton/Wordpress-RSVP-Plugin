<?php
namespace RSVPPlugin;

require_once ("ComponentRenderer.php");

abstract class PageRenderer extends ComponentRenderer
{

    protected $config;

    protected $componentRenderer;

    protected $renderContext;

    function __construct($pluginConfiguration)
    {
        parent::__construct($pluginConfiguration);
        
        $this->componentRenderer = null;
        
        add_filter('the_content', [
            $this,
            'OnRenderContent'
        ]);
    }

    public function OnRenderContent()
    {
        return $this->RenderComponentContent($this->renderContext);
    }

    public function RenderComponentContent($context)
    {
        if ($this->componentRenderer) {
            return $this->componentRenderer->OnRenderContent($context);
        }
        
        return "";
    }
}
