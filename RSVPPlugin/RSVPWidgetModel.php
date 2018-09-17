<?php


/**
 * RequestPayload short summary.
 *
 * RequestPayload description.
 *
 * @version 1.0
 * @author Lyle
 */

abstract class RSVPWidgetState
{
    const Invalid = -1;
    const PreAuth = 0;
    const ProcessAuth = 1;
    const EnterAttendance = 2;
    const ProcessAttendance = 3;
    const ProcessAllDoneSingle = 4;
    const AllDoneSingle = 5;

    const SelectPartyMember = 6;
    const EnterPartyMemberAttendance = 7;
    const ProcessPartyMemberAttendance = 8;
    const ProcessApplyContactDetails = 9;
    const ProcessAllDoneParty = 10;
    const AllDoneParty = 11;
}

class RSVPWidgetModel
{
    protected $action;
    protected $authId;
    protected $attendance;
    protected $associatedAttendees;
    protected $attendanceNotes;
    protected $attendeeId;
    protected $beverageOptions;
    protected $city;
    protected $dietaryReqs;
    protected $email;
    protected $firstName;
    protected $lastName;
    protected $mealOptions;
    protected $otherDietaryReqs;
    protected $phone;
    protected $postcode;
    protected $songRequest;
    protected $street;
    protected $rawPost;
    protected $errors;
    protected $successes;

    function __construct(  )
    {
        $this->action = RSVPWidgetState::PreAuth;
        $this->authId = "";
        $this->attendance = "";
        $this->attendanceNotes = "";
        $this->associatedAttendees = array();
        $this->attendeeId = "";
        $this->beverageOptions = "";
        $this->city = "";
        $this->dietaryReqs = "";
        $this->email = "";
        $this->firstName = "";
        $this->lastName = "";
        $this->mealOption = "";
        $this->otherDietaryReqs = "";
        $this->phone = "";
        $this->postCode = "";
        $this->songRequest = "";
        $this->street = "";
        $this->rawPost = $_POST;
        $this->ClearErrors();
        $this->ClearSuccesses();
    }

    public static function FromRequest() {
        $model = new RSVPWidgetModel();
        
        if( isset($_REQUEST['action']) )
        {
            switch ($_REQUEST['action'])
            {
                case '';
                    $model->SetAction(RSVPWidgetState::PreAuth);
                    break;
                case 'validate-authorise':
                    $model->SetAction(RSVPWidgetState::ProcessAuth);
                    break;
                case 'validate-attend':
                    $model->SetAction(RSVPWidgetState::ProcessAttendance);
                    break;
                case 'validate-partyselect':
                    $model->SetAction(RSVPWidgetState::ProcessPartyMemberAttendance);
                    break;
                case 'validate-apply-contact-details':
                    $model->SetAction(RSVPWidgetState::ProcessApplyContactDetails);
                    break;
                case 'validate-alldone-party':
                    $model->SetAction(RSVPWidgetState::ProcessAllDoneParty);
                break;
                case 'validate-single':
                    $model->SetAction(RSVPWidgetState::ProcessAllDoneSingle);
                break;
                default:
                    $model->SetAction(RSVPWidgetState::Invalid);
                    break;
            }
        }
        else
        {
            $model->SetAction(RSVPWidgetState::PreAuth);
        }

        if( isset($_REQUEST['authCtx']) )
        {
            try
            {
                list($encrypted_data, $iv) = explode('::', base64_decode($_REQUEST['authCtx']), 2);
                $authCtx = openssl_decrypt($encrypted_data, 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv);
                $model->SetAttendeeId($authCtx);
            }
            catch( Exception $ex )
            {
                $model->SetError( 'authCtx', 'Your session has expired' );
            }
        }

        if( isset($_REQUEST['firstName']) )
        {
            $model->SetFirstName( trim( $_REQUEST['firstName'] ) );
        }
        
        if( isset($_REQUEST['selectedAttendee']) )
        {
            try
            {
                list($encrypted_data, $iv) = explode('::', base64_decode($_REQUEST['selectedAttendee']), 2);
                $selectedAttendee = openssl_decrypt($encrypted_data, 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv);
                $model->SetAttendeeId($selectedAttendee);
            }
            catch( Exception $ex )
            {
                $model->SetError( 'selectedAttendee', 'Your session has expired' );
            }
        }

        if( isset($_REQUEST['lastName']) )
        {
            $model->SetLastName( trim ( $_REQUEST['lastName'] ) );
        }

        if( isset($_REQUEST['accessToken']) )
        {
            try
            {
                list($encrypted_data, $iv) = explode('::', base64_decode($_REQUEST['accessToken']), 2);
                $authId = openssl_decrypt($encrypted_data, 'aes-256-cbc', RSVPConfig::CIPHER_KEY, 0, $iv);
                $model->SetAuthId($authId);
            }
            catch( Exception $ex )
            {
                $model->SetError( 'authId', 'Your session has expired' );
            }
        }

        if( isset($_REQUEST['attendance']) )
        {
            $model->SetAttendance( trim ( $_REQUEST['attendance'] ) );
        }

        if( isset($_REQUEST['attendanceNotes']) )
        {
            $model->SetAttendanceNotes( trim ( $_REQUEST['attendanceNotes'] ) );
        }
        
        if( isset($_REQUEST['street']) )
        {
            $model->SetStreetAddress( trim ( $_REQUEST['street'] ) );
        }

        if( isset($_REQUEST['postcode']) )
        {
            $model->SetPostcode( trim ( $_REQUEST['postcode'] ) );
        }

        if( isset($_REQUEST['city']) )
        {
            $model->SetCity( trim ( $_REQUEST['city'] ) );
        }

        if( isset($_REQUEST['email']) )
        {
            $model->SetEmail( trim ( $_REQUEST['email'] ) );
        }

        if( isset($_REQUEST['phone']) )
        {
            $model->SetPhone( trim ( $_REQUEST['phone'] ) );
        }

        if( isset($_REQUEST['beverageOptions']) )
        {
            $model->SetBeverageOption( trim ( $_REQUEST['beverageOptions'] ) );
        }

        if( isset($_REQUEST['mealOptions']) )
        {
            $model->SetMealOption( trim ( $_REQUEST['mealOptions'] ) );
        }

        if( isset($_REQUEST['dietaryReqs']) )
        {
            $model->SetDietaryRequirement( trim ( $_REQUEST['dietaryReqs'] ) );
        }

        if( isset($_REQUEST['otherDietaryReqs']) )
        {
            $model->SetOtherDietaryRequirement( trim ( $_REQUEST['otherDietaryReqs'] ) );
        }

        if( isset($_REQUEST['songRequest']) )
        {
            $model->SetSongRequest( trim ( $_REQUEST['songRequest'] ) );
        }

        return $model;
    }

    function GetAction()
    {
        return $this->action;
    }

    function SetAction( $action )
    {
        $this->action = $action;
    }

    function GetAuthId()
    {
        return $this->authId;
    }

    function SetAuthId( $authId )
    {
        $this->authId = $authId;
    }

    function GetAttendeeId()
    {
        return $this->attendeeId;
    }

    function SetAttendeeId( $attendeeId )
    {
        $this->attendeeId = $attendeeId;
    }

    function GetAssociatedAttendees()
    {
        return $this->associatedAttendees;
    }

    function SetAssociatedAttendees( $associatedAttendees )
    {
        $this->associatedAttendees = $associatedAttendees;
    }

    function GetFirstName()
    {
        return $this->firstName;
    }

    function SetFirstName( $firstName )
    {
        $this->firstName = $firstName;
    }

    function GetLastName()
    {
        return $this->lastName;
    }

    function SetLastName( $lastName )
    {
        $this->lastName = $lastName;
    }

    function GetAttendance()
    {
        return $this->attendance;
    }

    function SetAttendance( $attendance )
    {
        $this->attendance = $attendance;
    }

    function GetEmail()
    {
        return $this->email;
    }

    function SetEmail( $email )
    {
        $this->email = $email;
    }

    function GetPhone()
    {
        return $this->phone;
    }

    function SetPhone( $phone )
    {
        $this->phone = $phone;
    }

    function GetStreetAddress()
    {
        return $this->street;
    }

    function SetStreetAddress( $street )
    {
        $this->street = $street;
    }

    function GetCity()
    {
        return $this->city;
    }

    function SetCity( $city )
    {
        $this->city = $city;
    }

    function GetPostcode()
    {
        return $this->postcode;
    }

    function SetPostcode( $postcode )
    {
        $this->postcode = $postcode;
    }

    function GetBeverageOption()
    {
        return $this->beverageOptions;
    }

    function SetBeverageOption( $beverageOptions )
    {
        $this->beverageOptions = $beverageOptions;
    }

    function GetMealOption()
    {
        return $this->mealOptions;
    }

    function SetMealOption( $mealOptions )
    {
        $this->mealOptions = $mealOptions;
    }

    function GetDietaryRequirement()
    {
        return $this->dietaryReqs;
    }

    function SetDietaryRequirement( $dietaryReqs )
    {
        $this->dietaryReqs = $dietaryReqs;
    }

    function GetOtherDietaryRequirement()
    {
        return $this->otherDietaryReqs;
    }

    function SetOtherDietaryRequirement( $otherDietaryReqs )
    {
        $this->otherDietaryReqs = $otherDietaryReqs;
    }

    function GetSongRequest()
    {
        return $this->songRequest;
    }

    function SetSongRequest( $songRequest )
    {
        $this->songRequest = $songRequest;
    }

    function GetAttendanceNotes()
    {
        return $this->attendanceNotes;
    }

    function SetAttendanceNotes( $attendanceNotes )
    {
        $this->attendanceNotes = $attendanceNotes;
    }

    function GetOtherAttribute( $attribName ) {
        if( isset( $this->rawPost[$attribName] ) ) {
            return $this->rawPost[$attribName];
        }
        return null;
    }

    function GetError( $field )
    {
        return $this->errors[$field];
    }

    function SetError( $field, $error )
    {
        $this->errors[$field] = $error;
    }
    
    function GetErrors( )
    {
        return $this->errors;
    }
    
    function ClearErrors( )
    {
        $this->errors = array();
    }

    function GetSuccess( $field )
    {
        return $this->successes[$field];
    }

    function SetSuccess( $field, $success )
    {
        $this->successes[$field] = $success;
    }
    
    function GetSuccesses( )
    {
        return $this->successes;
    }
    
    function ClearSuccesses( )
    {
        $this->successes = array();
    }

};
