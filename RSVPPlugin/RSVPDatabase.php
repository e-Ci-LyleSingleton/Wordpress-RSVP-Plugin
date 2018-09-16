<?php

function rsvp_database_get_associated_attendees_details_by_id( $attendeeId )
{
    // TODO
    return array (
        array ( 
        'firstName' => 'John',
        'lastName' => 'Citizen',
        'street' => '1 Sample Street',
        'city' => 'Samplevlle',
        'postcode' => '8830',
        'attendeeId' => 82,
        'attendance' => null,
        'attendanceNotes' => '',
        'beverageOptions' => 'alcoholic',
        'dietaryReqs' => '',
        'email' => 'sample@email.com',
        'mealOptions' => 'adult',
        'otherDietaryReqs' => '',
        'phone' => '0400000000',
        'songRequest' => ''
        ),
        array ( 
        'firstName' => 'Jane',
        'lastName' => 'Citizen',
        'street' => '1 Sample Street',
        'city' => 'Samplevlle',
        'postcode' => '8830',
        'attendeeId' => 84,
        'attendance' => true,
        'attendanceNotes' => '',
        'beverageOptions' => 'alcoholic',
        'dietaryReqs' => '',
        'email' => 'sample@citizencouncil.co.uk',
        'mealOptions' => 'adult',
        'otherDietaryReqs' => '',
        'phone' => '0400000000',
        'songRequest' => ''
        ),
        array ( 
        'firstName' => 'Joseph',
        'lastName' => 'Citizen',
        'street' => '1 Sample Street',
        'city' => 'Samplevlle',
        'postcode' => '8830',
        'attendeeId' => 85,
        'attendance' => false,
        'attendanceNotes' => null,
        'beverageOptions' => null,
        'dietaryReqs' => null,
        'email' => null,
        'mealOptions' => null,
        'otherDietaryReqs' => null,
        'phone' => null,
        'songRequest' => ''
        ),
    );
}


function rsvp_database_get_attendees_details_by_name( $firstName, $lastName )
{
    return array (
            array ( 
            'firstName' => 'John',
            'lastName' => 'Citizen',
            'street' => '1 Sample Street',
            'city' => 'Samplevlle',
            'postcode' => '8830',
            'attendeeId' => 82,
            'attendance' => null,
            'attendanceNotes' => '',
            'beverageOptions' => 'alcoholic',
            'dietaryReqs' => '',
            'email' => 'sample@email.com',
            'mealOptions' => 'adult',
            'otherDietaryReqs' => '',
            'phone' => '0400000000',
            'songRequest' => ''
            ),
    );
}


function rsvp_database_update_attendees_details_by_id( $attendeeId, $details )
{
    return true;
}

function rsvp_database_count_associated_attendees_details_by_id( $attendeeId )
{
    return array( 'count' => 3 );
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