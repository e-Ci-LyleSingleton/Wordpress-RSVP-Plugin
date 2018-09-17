<?php
/**
 *
 * @package wedding-rsvp
 * @subpackage  Uninstall
 * @author Lyle Singleton
 * @version 0.0.4
 * @license: GPL
 */
// To allow people to rsvp, create a new page or post and add "[wedding-rsvp-form]" to the text


// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

require_once( 'RSVPPlugin/RSVPConfig.php' );

// global $wpdb;
if( get_option(RSVP_OPTION_DELETE_DATA_ON_UNINSTALL) == "Y") {
	// Delete the tables
/*	
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "attendees" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "associatedAttendees" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rsvpCustomQuestions" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rsvpQuestionTypes" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "attendeeAnswers" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rsvpCustomQuestionAnswers" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rsvpCustomQuestionAttendees" );
	*/

	delete_option(RSVPConfig::OPTION_NAME_CURRENT_DB_VERSION);
}