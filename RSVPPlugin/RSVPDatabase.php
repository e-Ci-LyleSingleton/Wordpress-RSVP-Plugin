<?php
require_once( "RSVPConfig.php" );

function rsvp_database_get_attendees_attending()
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
            "SELECT `firstName`,
            `lastName`,
            `street`,
            `city`,
            `postcode`,
            `attendeeId`,
            `attendance`,
            `attendanceNotes`,
            `beverageOptions`,
            `dietaryReqs`,
            `email`,
            `mealOptions`,
            `otherDietaryReqs`,
            `phone`,
            `songRequest`
            FROM `$attendeesTable`
            WHERE `attendance` = 1"
    );        
}

function rsvp_database_get_attendees_not_attending()
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
            "SELECT `firstName`,
            `lastName`,
            `street`,
            `city`,
            `postcode`,
            `attendeeId`,
            `attendance`,
            `attendanceNotes`,
            `beverageOptions`,
            `dietaryReqs`,
            `email`,
            `mealOptions`,
            `otherDietaryReqs`,
            `phone`,
            `songRequest`
            FROM `$attendeesTable`
            WHERE `attendance` = 0"
    );        
}

function rsvp_database_get_attendees_no_response()
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
            "SELECT `firstName`,
            `lastName`,
            `street`,
            `city`,
            `postcode`,
            `attendeeId`,
            `attendance`,
            `attendanceNotes`,
            `beverageOptions`,
            `dietaryReqs`,
            `email`,
            `mealOptions`,
            `otherDietaryReqs`,
            `phone`,
            `songRequest`
            FROM `$attendeesTable`
            WHERE `attendance` IS NULL"
    );        
}

function rsvp_database_get_associated_attendees_details_by_id( $attendeeId )
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
        $wpdb->prepare( 
            "SELECT `t2`.`firstName`,
            `t2`.`lastName`,
            `t2`.`street`,
            `t2`.`city`,
            `t2`.`postcode`,
            `t2`.`attendeeId`,
            `t2`.`attendance`,
            `t2`.`attendanceNotes`,
            `t2`.`beverageOptions`,
            `t2`.`dietaryReqs`,
            `t2`.`email`,
            `t2`.`mealOptions`,
            `t2`.`otherDietaryReqs`,
            `t2`.`phone`,
            `t2`.`songRequest`
            FROM `$attendeesTable` AS t1, `$attendeesTable` AS t2
            WHERE `t1`.`partyId` = `t2`.`partyId`
            AND `t1`.`attendeeId` = %d AND `t1`.`partyId` IS NOT NULL;", array( $attendeeId )
        )
    );        
}


function rsvp_database_get_attendees_details_by_name( $firstName, $lastName )
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
        $wpdb->prepare( 
            "SELECT `firstName`, `lastName`, `street`, `city`, `postcode`, `attendeeId`, `attendance`, `attendanceNotes`, `beverageOptions`, `dietaryReqs`, `email`, `mealOptions`, `otherDietaryReqs`, `phone`, `songRequest`
            FROM $attendeesTable WHERE `firstName` LIKE %s AND `lastName` LIKE %s", array( $firstName, $lastName )
        )
    );
}


function rsvp_database_update_attendees_details_by_id( 
    $attendeeId,
    $details
    )
{


    if( is_array( $details ) )
    {
        $insertValues = array();
        $insertFormat = array();

        if( isset( $details['firstName'] ) )
        {
            $insertValues['firstName'] = $details['firstName'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['lastName'] ) )
        {
            $insertValues['lastName'] = $details['lastName'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['attendance'] ) )
        {
            $insertValues['attendance'] = null;
            if( $details['attendance'] != '' )
            {
                $insertValues['attendance'] = $details['attendance'];
                array_push( $insertFormat, '%d' );
            }
            else
            {
                array_push( $insertFormat, null );
            }
        }
        if( isset( $details['email'] ) )
        {
            $insertValues['email'] = $details['email'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['phone'] ) )
        {
            $insertValues['phone'] = $details['phone'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['street'] ) )
        {
            $insertValues['street'] = $details['street'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['city'] ) )
        {
            $insertValues['city'] = $details['city'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['postcode'] ) )
        {
            $insertValues['postcode'] = $details['postcode'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['beverageOptions'] ) )
        {
            $insertValues['beverageOptions'] = $details['beverageOptions'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['mealOptions'] ) )
        {
            $insertValues['mealOptions'] = $details['mealOptions'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['dietaryReqs'] ) )
        {
            $insertValues['dietaryReqs'] = $details['dietaryReqs'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['otherDietaryReqs'] ) )
        {
            $insertValues['otherDietaryReqs'] = $details['otherDietaryReqs'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['songRequest'] ) )
        {
            $insertValues['songRequest'] = $details['songRequest'];
            array_push( $insertFormat, '%s' );
        }
        if( isset( $details['attendanceNotes'] ) )
        {
            $insertValues['attendanceNotes'] = $details['attendanceNotes'];
            array_push( $insertFormat, '%s' );
        }

        global $wpdb;
        $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
        $result = $wpdb->update($attendeesTable, $insertValues, array( 'attendeeId' => $attendeeId), $insertFormat, '%d');
        return $result === 1 || ($result === 0 && $wpdb->result == true );
    }
    return false;
}

function rsvp_database_count_associated_attendees_details_by_id( $attendeeId )
{
    global $wpdb;
    $attendeesTable = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    return $wpdb->get_results(
        $wpdb->prepare( "SELECT COUNT(*) as `count`
        FROM `$attendeesTable` AS t1, `$attendeesTable` AS t2
        WHERE `t1`.`partyId` = `t2`.`partyId`
        AND `t1`.`attendeeId` = %d AND `t1`.`partyId` IS NOT NULL;", array( $attendeeId ) ) );
}

function rsvp_database_coerce_bool_to_string( $value )
{
    if( $value === true )
    {
        return '1';
    }
    return '0';
}

function rsvp_database_coerce_tristate_to_string_or_empty( $value )
{
    if( $value === true )
    {
        return '1';
    }
    else if( $value === false )
    {
        return '0';
    }
    return '';
}

function rsvp_database_coerce_null_to_string( $value )
{
    if( isset( $value) )
    {
        return strval( $value );
    }
    return '';
}