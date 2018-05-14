<?php
namespace RSVPPlugin;

require_once("PageRenderer.php");

if ( !class_exists( 'ClientFormComplete' ) ) {
    class ClientFormComplete extends PageRenderer
    {
        function __construct( $pluginConfiguration )
        {
            parent::__construct( $pluginConfiguration );
        }
    }
}

?>