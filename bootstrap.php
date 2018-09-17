<?php
/**
 *
 * @package wedding-rsvp
 * @author Lyle Singleton
 * @version 0.0.3
 */
/*
 * Plugin Name: WeddingRSVP
 * Text Domain: wedding-rsvp
 * Plugin URI: http://wordpress.org/extend/plugins/weddingrsvp/
 * Description: This plugin allows guests to RSVP to a wedding but.
 * Author: Lyle Singleton
 * Version: 0.0.3
 * Author URI: http://www.sineadandlylesingleton.com
 * License: GPL
 */

// To allow people to rsvp, create a new page or post and add "[wedding-rsvp-form]" to the text

require_once( 'RSVPPlugin/RSVPConfig.php' );
require_once( 'RSVPPlugin/RSVPLifecycle.php' );
require_once( 'RSVPPlugin/RSVPWidget.php' );


register_activation_hook(__FILE__, 'rsvp_lifecycle_on_activate');
rsvp_lifecycle_register_plugin();
rsvp_widget_register( RSVPConfig::SHORTCODE_TAG );

?>
