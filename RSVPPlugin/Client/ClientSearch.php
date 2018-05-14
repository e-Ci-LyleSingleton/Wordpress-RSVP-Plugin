<?php
namespace RSVPPlugin;

require_once("PageRenderer.php");

if ( !class_exists( 'ClientSearch' ) ) {
    class ClientSearch extends ComponentRenderer
    {
        function __construct( $pluginConfiguration )
        {
            parent::__construct( $pluginConfiguration );
        }

        public function RenderComponentContent($context)
        {
            return "Search";
        }
    }
}

?>