<?php
require_once 'RSVPWidgetModel.php';
require_once 'RSVPDatabase.php';

function rsvp_widget_has_errors( $errors )
{
    return count( $errors ) != 0; 
}

function rsvp_widget_apply_attendee_details_to_request( $attendeeDetails, $request )
{
    $request->SetAttendance( rsvp_database_coerce_null_to_string( $attendeeDetails->attendance ) );
    $request->SetAttendeeId( rsvp_database_coerce_null_to_string( $attendeeDetails->attendeeId ) );
    $request->SetFirstName( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->firstName ) ) );
    $request->SetLastName( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->lastName ) ) );
    rsvp_widget_apply_contact_details_to_request( $attendeeDetails, $request );
    $request->SetMealOption( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->mealOptions ) ) );
    $request->SetBeverageOption( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->beverageOptions ) ) );
    $request->SetDietaryRequirement( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->dietaryReqs ) ) );
    $request->SetOtherDietaryRequirement( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->otherDietaryReqs ) ) );
    $request->SetSongRequest( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->songRequest ) ) );
    $request->SetAttendanceNotes( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->attendanceNotes ) ) );
}

function rsvp_widget_apply_contact_details_to_request( $attendeeDetails, $request )
{
    $request->SetEmail( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->email ) ) );
    $request->SetPhone( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->phone ) ) );
    $request->SetStreetAddress( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->street ) ) );
    $request->SetCity( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->city ) ) );
    $request->SetPostcode( stripslashes ( rsvp_database_coerce_null_to_string( $attendeeDetails->postcode ) ) );
}

function rsvp_widget_request_to_attendee_details( $request )
{
    $attendeeDetails = array();
    $attendeeDetails['attendeeId'] = $request->GetAttendeeId();
    $attendeeDetails['attendance'] = 'NULL';
    $attendance = $request->GetAttendance();
    if( $attendance === '1' || $attendance === '0' )
    {
        $attendeeDetails['attendance'] = $attendance;
    }
    $attendeeDetails['firstName'] = $request->GetFirstName();
    $attendeeDetails['lastName'] = $request->GetLastName();
    $attendeeDetails['attendance'] = $request->GetAttendance();
    $attendeeDetails['email'] = $request->GetEmail();
    $attendeeDetails['phone'] = $request->GetPhone();
    $attendeeDetails['street'] = $request->GetStreetAddress();
    $attendeeDetails['city'] = $request->GetCity();
    $attendeeDetails['postcode'] = $request->GetPostcode();
    $attendeeDetails['beverageOptions'] = $request->GetBeverageOption();
    $attendeeDetails['mealOptions'] = $request->GetMealOption();
    $attendeeDetails['dietaryReqs'] = $request->GetDietaryRequirement();
    $attendeeDetails['otherDietaryReqs'] = $request->GetOtherDietaryRequirement();
    $attendeeDetails['songRequest'] = $request->GetSongRequest();
    $attendeeDetails['attendanceNotes'] = $request->GetAttendanceNotes();
    return $attendeeDetails;
}

function rsvp_widget_render_plugin() {

    $request = RSVPWidgetModel::FromRequest();

    if( $_SERVER["REQUEST_METHOD"] == "POST" )
    {
        rsvp_widget_process_action_recursive($request);
    }
    else
    {
        return rsvp_widget_render_preauth( $request->GetFirstName(), $request->GetLastName(), $request->GetErrors() );
    }
}

function rsvp_widget_preauth_validate( $request )
{
    $nonceAction = 'authorise';
    $nonceName = 'rsvp-widget';
    $nonceVal = $request->GetOtherAttribute($nonceName);
    if( !wp_verify_nonce( $nonceVal, $nonceAction ) )
    {
        $request->SetError('sessiontoken', 'Your session has expired');
    }

    if( strlen( $request->GetFirstName() ) < 2 )
    {
        $request->SetError('firstName', 'Please enter your first name');
    }
    
    if( strlen( $request->GetLastName() ) < 2 )
    {
        $request->SetError('lastName', 'Please enter your last name');
    }

    if( !rsvp_widget_has_errors( $request->GetErrors() ) )
    {
        $matchingAttendees = rsvp_database_get_attendees_details_by_name( $request->GetFirstName(), $request->GetLastName() );

        $matchCount = count( $matchingAttendees );
        if( $matchCount == 1 )
        {
            rsvp_widget_apply_attendee_details_to_request( $matchingAttendees[0], $request );
            $request->SetAuthId( rsvp_database_coerce_null_to_string( $matchingAttendees[0]->attendeeId ) );
        }
        else if( $matchCount == 0 )
        {
            $request->SetError('values', 'Could not find a match for the supplied values');
        }
        else
        {
            $request->SetError('internalError', 'An internal server error has occured. You can thank Lyle for that :)');
        }
    }

    return !rsvp_widget_has_errors( $request->GetErrors() ) ;
}

function rsvp_widget_render_preauth( $firstName, $lastName, $errors )
{
    $nonceAction = 'authorise';
    $nonceName = 'rsvp-widget';
    wp_create_nonce( $nonceAction, $nonceName );
    include( "Fragments/AccessToken.fragment.php" );
}

function rsvp_widget_attendance_validate( $request )
{
    $nonceAction = 'authorise';
    $nonceName = 'rsvp-widget';
    $nonceVal = $request->GetOtherAttribute($nonceName);
    if( !wp_verify_nonce( $nonceVal, $nonceAction ) )
    {
        $request->SetError('sessiontoken', 'Your session has expired');
    }
    
    if( strlen( $request->GetFirstName() ) < 2 )
    {
        $request->SetError('firstName', 'Please enter your full first name');
    }
    
    if( strlen( $request->GetLastName() ) < 2 )
    {
        $request->SetError('lastName', 'Please enter your full last name');
    }
    
    if( strlen( $request->GetPhone() ) < 2 )
    {
        $request->SetError('phone', 'Please enter your phone number');
    }
    
    if( $request->GetAttendance() == '0' )
    {
        ;
    }
    else if( $request->GetAttendance() == '1' )
    {
        if( strlen( $request->GetStreetAddress() ) < 2 )
        {
            $request->SetError('street', 'Please enter your street address');
        }
        
        if( strlen( $request->GetCity() ) < 2 )
        {
            $request->SetError('city', 'Please enter your city');
        }
        
        if( strlen( $request->GetPostcode() ) < 2 )
        {
            $request->SetError('postcode', 'Please enter your postcode');
        }
        
        if( !( $request->GetBeverageOption() == 'alcoholic' || $request->GetBeverageOption() == 'non-alcoholic' ) )
        {
            $request->SetError('beverageOptions', 'Please indicate your choice of refreshments');
        }
        
        if( !( $request->GetMealOption() == 'adult' || $request->GetMealOption() == 'child' ) )
        {
            $request->SetError('mealOptions', 'Please indicate your choice of meal options');
        }
        
        switch( $request->GetDietaryRequirement() )
        {
            case '':
            case 'glutenfree':
            case 'vegetarian':
            case 'vegan':
                break;
            case 'other':
                if( strlen( $request->GetOtherDietaryRequirement() ) < 2 )
                {
                    $request->SetError('otherDietaryReqs', 'Please enter your \'other\' dietary requirements');
                }
                break;
            default:
                $request->SetError('dietaryReqs', 'You have not indicated your dietary requirements');
        }
    }
    else
    {
        $request->SetError('attendance', 'You have not indicated your attendance');
    }
   
    if( !rsvp_widget_has_errors( $request->GetErrors() ) )
    {
        if( !rsvp_database_update_attendees_details_by_id( 
            $request->GetAttendeeId(), 
            rsvp_widget_request_to_attendee_details( $request ) ) )
        {
            $request->SetError('internalError', 'An internal server error has occured. You can thank Lyle for that :)');
        };
    }
    return !rsvp_widget_has_errors( $request->GetErrors() );
}

function rsvp_widget_render_attendance_form_from_request($request)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $authCtx = base64_encode( openssl_encrypt( $request->GetAttendeeId(), 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv ) . '::' . $iv);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $accessToken = base64_encode( openssl_encrypt( $request->GetAuthId(), 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv ) . '::' . $iv);
    
    return rsvp_widget_render_attendance_form( 
        $request->GetFirstName(),
        $request->GetLastName(),
        $request->GetAttendance(),
        $request->GetEmail(),
        $request->GetPhone(),
        $request->GetStreetAddress(),
        $request->GetCity(),
        $request->GetPostcode(),
        $request->GetBeverageOption(),
        $request->GetMealOption(),
        $request->GetDietaryRequirement(),
        $request->GetOtherDietaryRequirement(),
        $request->GetSongRequest(),
        $request->GetAttendanceNotes(),
        $authCtx,
        $accessToken,
        $request->GetErrors(),
        $request->GetSuccesses()
    );
} 

function rsvp_widget_render_attendance_form( 
    $firstName,
     $lastName,
     $attendance,
     $email,
     $phone,
     $street,
     $city,
     $postcode,
     $beverageOptions,
     $mealOptions,
     $dietaryReqs,
     $otherDietaryReqs,
     $songRequest,
     $attendanceNotes,
     $authCtx,
     $accessToken,
     $errors,
     $successes )
{

    $nonceAction = 'authorise';
    $nonceName = 'rsvp-widget';
    wp_create_nonce( $nonceAction, $nonceName );

    include( "Fragments/RSVPForm.fragment.php" );
}

function rsvp_widget_render_party_member_select_from_request( $request )
{
    $associatedAttendees = rsvp_database_get_associated_attendees_details_by_id( $request->GetAuthId() );
    foreach ($associatedAttendees as &$attendee) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $authCtx = base64_encode( openssl_encrypt( $attendee->attendeeId, 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv ) . '::' . $iv);
        $attendee = (object) array_merge( (array)$attendee, array( 'authCtx' => $authCtx ) );
    }
    $request->SetAssociatedAttendees( $associatedAttendees );
    
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $accessToken = base64_encode( openssl_encrypt( $request->GetAuthId(), 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv ) . '::' . $iv);

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $authCtx = base64_encode( openssl_encrypt( $request->GetAttendeeId(), 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv ) . '::' . $iv);

    return rsvp_widget_render_party_member_select( 
        $request->GetAssociatedAttendees(), 
        $authCtx,
        $accessToken,
        $request->GetErrors(),
        $request->GetSuccesses()
    );
}

function rsvp_widget_process_apply_contact_details( $request )
{
    $nonceAction = 'member-select';
    $nonceName = 'rsvp-widget';
    $nonceVal = $request->GetOtherAttribute($nonceName);
    if( !wp_verify_nonce( $nonceVal, $nonceAction ) )
    {
        $request->SetError('sessiontoken', 'Your session has expired');
    }

    if( $request->GetAuthId() == $request->GetAttendeeId() )
    {
        $request->SetError('authCtx', 'You cannot apply your own contact details to yourself, silly!');
    }
    
    if( !rsvp_widget_has_errors( $request->GetErrors() ) )
    {
        $associatedAttendees = rsvp_database_get_associated_attendees_details_by_id( $request->GetAuthId() );
        
        $currentUserAttendanceDetails = null;
        $attendeeDetails = null;

        foreach ($associatedAttendees as $attendee)
        {
            if( $attendee->attendeeId == $request->GetAttendeeId() )
            {
                $attendeeDetails = $attendee;
            }
            else if( $attendee->attendeeId == $request->GetAuthId() )
            {
                $currentUserAttendanceDetails = $attendee;
            }

            // we have found both if the users needed
            if( $currentUserAttendanceDetails !== null && $attendeeDetails !== null )
            {
                rsvp_widget_apply_attendee_details_to_request( $attendee, $request );
                rsvp_widget_apply_contact_details_to_request( $currentUserAttendanceDetails, $request );
                break;
            }
        }

        if( $currentUserAttendanceDetails === null || $attendeeDetails === null )
        {
            $request->SetError('authCtx', 'Your session has expired');
        }
        else 
        {
            if( !rsvp_database_update_attendees_details_by_id( 
                $request->GetAttendeeId(), 
                rsvp_widget_request_to_attendee_details( $request ) ) )
            {
                $request->SetError('internalError', 'An internal server error has occured. You can thank Lyle for that :)');
            };
        }
    }
    
    return !rsvp_widget_has_errors( $request->GetErrors() ) ;
}

function rsvp_widget_render_party_member_select(
    $associatedAttendees,
    $authCtx,
    $accessToken,
    $errors,
    $successes )
{

    $nonceAction = 'member-select';
    $nonceName = 'rsvp-widget';
    wp_create_nonce( $nonceAction, $nonceName );

    include( "Fragments/AttendeeSelection.fragment.php" );
}

function rsvp_widget_process_party_member_select( $request )
{
    $nonceAction = 'member-select';
    $nonceName = 'rsvp-widget';
    $nonceVal = $request->GetOtherAttribute($nonceName);
    if( !wp_verify_nonce( $nonceVal, $nonceAction ) )
    {
        $request->SetError('sessiontoken', 'Your session has expired');
    }
    
    if( !rsvp_widget_has_errors( $request->GetErrors() ) )
    {
        $associatedAttendees = rsvp_database_get_associated_attendees_details_by_id( $request->GetAuthId() );

        $matchFound = false;

        $previousRequest = $request;
        foreach ($associatedAttendees as $attendee)
        {
            if( $attendee->attendeeId == $request->GetAttendeeId() )
            {
                $matchFound = true;
                rsvp_widget_apply_attendee_details_to_request( $attendee, $request );
                break;
            }
        }

        if( !$matchFound )
        {
            $request->SetError('authCtx', 'Your session has expired');
        }
    }
 
    return !rsvp_widget_has_errors( $request->GetErrors() ) ;
}

function rsvp_widget_process_party_all_done( $request )
{
    if( !rsvp_widget_has_errors( $request->GetErrors() ) )
    {
        $associatedAttendees = rsvp_database_get_associated_attendees_details_by_id( $request->GetAuthId() );
        $request->SetAssociatedAttendees( $associatedAttendees );

        $matchFound = false;

        $previousRequest = $request;
        foreach ($associatedAttendees as $attendee)
        {
            if( $attendee->attendeeId == $request->GetAuthId() )
            {
                $matchFound = true;
                rsvp_widget_apply_attendee_details_to_request( $attendee, $request );
                break;
            }
        }

        if( !$matchFound )
        {
            $request->SetError('internalError', 'An internal server error has occured. You can thank Lyle for that :)');
        }
    }
      
    return !rsvp_widget_has_errors( $request->GetErrors() );
}

function rsvp_widget_render_party_all_done_from_request( $request )
{
    return rsvp_widget_render_party_all_done( $request->GetFirstName(), $request->GetAttendance(), $request->GetAssociatedAttendees(), $request->GetErrors() );
}

function rsvp_widget_render_party_all_done( $firstName, $attendance, $associatedAttendees, $errors )
{
    include( "Fragments/AllDone.Party.fragment.php" );
}

function rsvp_widget_process_single_all_done( $request )
{
    return true;
}

function rsvp_widget_render_single_all_done_from_request( $request )
{
    return rsvp_widget_render_single_all_done(  $request->GetFirstName(), $request->GetAttendance(), $request->GetErrors(), $request->GetSuccesses() );
}

function rsvp_widget_render_single_all_done( $firstName, $attendance, $errors, $successes )
{
    include( "Fragments/AllDone.Single.fragment.php" );
}

function rsvp_widget_process_action_recursive($request) 
{
    switch ( $request->GetAction() )
    {
        case RSVPWidgetState::ProcessAuth:
            if( rsvp_widget_preauth_validate( $request ) )
            {
                $associatedAttendees = rsvp_database_count_associated_attendees_details_by_id( $request->GetAttendeeId() );

                if( $associatedAttendees[0]->count > 1 )
                {
                    $request->SetAction(RSVPWidgetState::SelectPartyMember);
                }
                else
                {
                    $request->SetAction(RSVPWidgetState::EnterAttendance);
                }
            }
            else
            {
                $request->SetAction(RSVPWidgetState::PreAuth);
            }
            break;
        case RSVPWidgetState::ProcessAttendance:
            if( rsvp_widget_attendance_validate( $request ) )
            {
                $request->SetSuccess( 'attendee', 'Your changes have been saved' );
                $associatedAttendees = rsvp_database_count_associated_attendees_details_by_id( $request->GetAttendeeId() );

                if( $associatedAttendees[0]->count > 1 )
                {
                    $request->SetAction(RSVPWidgetState::SelectPartyMember);
                }
                else
                {
                    $request->SetAction(RSVPWidgetState::AllDoneSingle);
                }
            } 
            else 
            {
                $request->SetAction(RSVPWidgetState::EnterAttendance);
            }
            break;
        case RSVPWidgetState::ProcessPartyMemberAttendance:
            if( rsvp_widget_process_party_member_select( $request ) )
            {
                $request->SetAction(RSVPWidgetState::EnterAttendance);
            }
            else
            {
                $request->SetAction(RSVPWidgetState::SelectPartyMember);
            }
            break;
        case RSVPWidgetState::ProcessApplyContactDetails:
            if( rsvp_widget_process_apply_contact_details( $request ) )
            {
                $request->SetSuccess( 'contactDetails', 'Your changes have been saved' );
            }
            else
            {
                $request->SetAction(RSVPWidgetState::SelectPartyMember);
            }
            break;
        case RSVPWidgetState::ProcessAllDoneSingle:
            if( rsvp_widget_process_single_all_done( $request ) )
            {
                $request->SetAction(RSVPWidgetState::AllDoneSingle);
            }
            else
            {
                $request->SetAction(RSVPWidgetState::EnterAttendance);
            }
            break;
        case RSVPWidgetState::ProcessAllDoneParty:
            if( rsvp_widget_process_party_all_done( $request ) )
            {
                $request->SetAction(RSVPWidgetState::AllDoneParty);
            }
            else
            {
                $request->SetAction(RSVPWidgetState::EnterAttendance);
            }
            break;
        case RSVPWidgetState::EnterAttendance:
            return rsvp_widget_render_attendance_form_from_request( $request );
        case RSVPWidgetState::SelectPartyMember:
            return rsvp_widget_render_party_member_select_from_request( $request );
        case RSVPWidgetState::AllDoneParty:
            return rsvp_widget_render_party_all_done_from_request( $request );
        case RSVPWidgetState::AllDoneSingle:
            return rsvp_widget_render_single_all_done_from_request( $request );
        case RSVPWidgetState::PreAuth:
        case RSVPWidgetState::Invalid:
        default:
            return rsvp_widget_render_preauth( $request->GetFirstName(), $request->GetLastName(), $request->GetErrors() );
    }
    
    return rsvp_widget_process_action_recursive($request);
}

function rsvp_widget_inject_client_scripts()
{
    wp_enqueue_style( 'w3_css_4' );
    wp_enqueue_style( 'font_awesome_5_3_1' );
}

function rsvp_widget_register( $shortcode )
{
    add_shortcode( $shortcode, 'rsvp_widget_render_plugin' );

    add_action( 'wp_enqueue_scripts', 'rsvp_widget_inject_client_scripts' );
}
