<?php
require_once( __DIR__.'\..\RSVPDatabase.php' );
require_once( __DIR__.'\..\RSVPConfig.php' );

function rsvp_admin_render_home( $errors, $successes, $attendees, $absentees, $awaiting )
{
    include( "Fragments/RSVPList.fragment.php" );
}

function rsvp_admin_home()
{
    $errors = array();
    $successes = array();
    $rows = array();
    
    $attending = rsvp_database_get_attendees_attending();
    $notAttending = rsvp_database_get_attendees_not_attending();
    $noResponse = rsvp_database_get_attendees_no_response();

    return rsvp_admin_render_home( $errors, $successes, $attending, $notAttending, $noResponse );
}
