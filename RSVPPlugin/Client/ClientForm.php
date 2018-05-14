<?php
namespace RSVPPlugin;

require_once("PageRenderer.php");

    
    abstract class RsvpStatus
    {
        const NoResponse = 1;
        const Accepted = 2;
        const Declined = 3;
    }

    abstract class ClientForm extends ComponentRenderer
    {
        function __construct( $pluginConfiguration )
        {
            parent::__construct( $pluginConfiguration );
        }
        
        abstract public function RenderComponentContent($context);
    }


?>