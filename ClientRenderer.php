<?php
namespace RSVPPlugin;

require_once("Config.php");

if ( !class_exists( 'ClientRenderer' ) ) {

    abstract class SubmissionState
    {
        const LandingPage = 1;
        const FindByNamePasscode = 2;
        const FindByPasscode = 3;
        const FindByName = 4;
        const NewRsvp = 5;
        const AmendRsvp = 6;
        const ProcesRsvp = 7;
        const AcknowledgeSubmission = 8;
        const RsvpNotFound = 9;
    }

    abstract class AuthMethod
    {
        const WPUser = 1;
        const NamePasscode = 2;
        const Passcode = 3;
        const Name = 4;
    }

    abstract class RsvpStatus
    {
        const NoResponse = 1;
        const Accepted = 2;
        const Declined = 3;
    }

    class ClientRenderer
    {
        private $config;

        function __construct( $pluginConfiguration )
        {
            $this->config = $pluginConfiguration;

			add_action('wp_head', [$this,'RenderCustomCSS']);
            
			add_shortcode( 'rsvp', $this->config->GetShortCode() );

			add_filter('the_content', [$this,'Render']);
        }

        public function Render()
        {           
            $requestContext = $this->GetRequestContext();
            $authenticationContext = $this->GetAuthenticationContext( $requestContext );
            $authorisationContext = $this->GetAuthorisationContext( $requestContext );

            $this->FindRsvpForRequestContext( $requestContext, $authenticationContext, $authorisationContext );

            return $this->RenderRequest( $requestContext, $authenticationContext, $authorisationContext );
        }

        private function GetAuthorisationContext( $requestContext )
        {
            $securityContext["authMethodPriority"] = [];
            $securityContext["ignoreWpUserAuthAllowed"] = false;
            
            if( $requestContext["wpUserId"] )
            {
                $user = wp_get_current_user();
    
                if( in_array( get_option( Config::OPTION_CONFIG_ADMINISTRATOR_ROLE ), $user->roles ) )
                {
                    $securityContext["ignoreWpUserAuthAllowed"] = true;
                }
                $securityContext["authMethodPriority"][] = AuthMethod::WPUser;
            }

            if( get_option( Config::OPTION_CONFIG_ALLOW_UNAUTHENTICATED ) == "Y" )
            {
                if(  get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE ) == "Y" )
                {
                    $securityContext["authMethodPriority"][] = AuthMethod::NamePasscode;
                }
                if( get_option( Config::OPTION_CONFIG_ALLOW_PASSCODE ) == "Y" )
                {
                    $securityContext["authMethodPriority"][] = AuthMethod::Passcode;
                }
                if( get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH ) == "Y" )
                {
                    $securityContext["authMethodPriority"][] = AuthMethod::Name;
                }
            }
            
            return $securityContext;
        }

        private function GetAuthenticationContext( $requestContext )
        {
            $authContext["passcode"] = "";
            $authContext["firstName"] = "";
            $authContext["lastName"] = "";
            $authContext["availableMethods"] = [];

            // Allow administrators to override the preferred authentication mechanism,
            // Not yet implemented
            
            if( $requestContext["wpUserId"] )
            {
                $user = wp_get_current_user();
                $useSecondaryAuthorisation = false;
                if( in_array( get_option( Config::OPTION_CONFIG_ADMINISTRATOR_ROLE ), $user->roles ) )
                {
                    $useSecondaryAuthorisation = $requestContext["useSecondaryAuth"];
                }

                if( !$useSecondaryAuthorisation )
                {
                    $authContext["availableMethods"][] = AuthMethod::WPUser;
                }
            }

            if( count($requestContext["passcode"]) >= get_option( Config::OPTION_CONFIG_PASSCODE_MIN_LENGTH ) )
            {
                $authContext["passcode"] = $requestContext["passcode"];
                $authContext["availableMethods"][] = AuthMethod::Passcode;
            }

            if( count($requestContext["firstName"]) > 0 && count($requestContext["lastName"]) > 0 )
            {   
                $authContext["firstName"] = $requestContext["firstName"];
                $authContext["lastName"] = $requestContext["lastName"];
                $authContext["availableMethods"][] = AuthMethod::Name;
            }

            if( in_array( AuthMethod::Name, $authContext["availableMethods"] ) &&
                in_array( AuthMethod::Passcode, $authContext["availableMethods"] ) )
             {
                $authContext["availableMethods"][] = AuthMethod::NamePasscode;
             }

            return $authContext;
        }

        private function GetRequestContext()
        {
            $requestContext["firstName"] = null;
            $requestContext["lastName"] = null;
            $requestContext["passcode"] = null;
            $requestContext["contactPhone"] = null;
            $requestContext["email"] = null;
            $requestContext["rsvpId"] = null;
            $requestContext["rsvpDate"] = null;
            $requestContext["rsvpStatus"] = null;
            $requestContext["note"] = null;
            $requestContext["kidsMeal"] = null;
            $requestContext["veggieMeal"] = null;
            $requestContext["additionalAttendee"] = null;
            $requestContext["personalGreeting"] = null;
            $requestContext["wpUserId"] = null;
            $requestContext["associatedAttendees"] = [];
            $requestContext["useSecondaryAuth"] = false;
            $requestContext["action"] = SubmissionState::LandingPage;

            if( isset($_POST["useSecondaryAuth"]) )
			{
                $requestContext["useSecondaryAuth"] = $_POST["useSecondaryAuth"];
			}

            if( isset($_POST["firstName"]) )
			{
				$requestContext["firstName"] = $_POST["firstName"];
			}

            if( isset($_POST["lastName"]) )
			{
				$requestContext["lastName"] = $_POST["lastName"];
			}

            if( isset($_POST["passcode"]) )
			{
				$requestContext["passcode"] = $_POST["passcode"];
			}

            if( isset($_POST["contactPhone"]) )
			{
				$requestContext["contactPhone"] = $_POST["contactPhone"];
			}

            if( isset($_POST["email"]) )
			{
				$requestContext["email"] = $_POST["email"];
			}

            if( isset($_POST["rsvpDate"]) )
			{
				$requestContext["rsvpDate"] = $_POST["rsvpDate"];
			}

            if( isset($_POST["note"]) )
			{
				$requestContext["note"] = $_POST["note"];
			}

            if( isset($_POST["kidsMeal"]) )
			{
				$requestContext["kidsMeal"] = $_POST["kidsMeal"];
			}

            if( isset($_POST["veggieMeal"]) )
			{
				$requestContext["veggieMeal"] = $_POST["veggieMeal"];
			}

            if( isset($_POST["additionalAttendee"]) )
			{
				$requestContext["additionalAttendee"] = $_POST["additionalAttendee"];
			}

            if( isset($_POST["personalGreeting"]) )
			{
				$requestContext["personalGreeting"] = $_POST["personalGreeting"];
            }

            if( isset($_POST["rsvpStatus"]) )
			{
                switch ($_POST["rsvpStatus"]) {
                    case "0":
                        $requestContext["rsvpStatus"] = RsvpStatus::NoResponse;
                        break;
                    case "1":
                        $requestContext["rsvpStatus"] = RsvpStatus::Accepted;
                        break;
                    case "2":
                        $requestContext["rsvpStatus"] = RsvpStatus::Declined;
                        break;
                    default:
                        break;
                }
            }
            
            if( is_user_logged_in() )
            {
                $user = wp_get_current_user();
                $requestContext["wpUserId"] = $user->ID;
            }

            return $requestContext;            
        }
        
        private function FindRsvpByUserId( $wpUserId )
        {
            $rsvp = $this->config->GetDatabase()->get_row(
                $this->config->GetDatabase()->prepare(
                    "SELECT id
                    FROM ".$this->config->AttendeesTable()."
                    WHERE wpUserId = %s", $wpUserId));
            return $rsvp;
        }

        private function FindRsvpByPasscode( $passcode )
        {
            $rsvp = $this->config->GetDatabase()->get_row(
                $this->config->GetDatabase()->prepare(
                    "SELECT id
                    FROM ".$this->config->AttendeesTable()."
                    WHERE passcode = %s", $passcode));
            return $rsvp;
        }

        private function FindRsvpByNamePasscode( $firsName, $lastName, $passcode )
        {
            $rsvp = $this->config->GetDatabase()->get_row(
                $this->config->GetDatabase()->prepare(
                    "SELECT id
                    FROM ".$this->config->AttendeesTable()."
                    WHERE firstName = %s AND lastName = %s AND passcode = %s", $firstName, $lastName, $passcode));

                return $rsvp;
        }

        private function FindRsvpByName( $firsName, $lastName )
        {
            $rsvp = $this->config->GetDatabase()->get_row(
                $this->config->GetDatabase()->prepare(
                    "SELECT id
                    FROM ".$this->config->AttendeesTable()."
                    WHERE firstName = %s AND lastName = %s", $firstName, $lastName));

                return $rsvp;
        }

        private function FindRsvpForRequestContext( $requestContext, $authenticationContext, $authorisationContext )
        {
            $canBeAuthorised = count( $authenticationContext["availableMethods"]) > 0;
            
            if( $canBeAuthorised )
            {
                $canBeAuthenticated = false;
                $rsvpId = null;

                for ($i=0; $i < count( $authorisationContext["authMethodPriority"]) && $rsvpId == null; $i++)
                {
                    $authenticationMethod = $authorisationContext["authMethodPriority"][$i];
                    
                    if( in_array( $authenticationMethod, $authenticationContext["availableMethods"] ) )
                    {
                        $canBeAuthenticated = true;

                        switch ($authenticationMethod) {
                            case AuthMethod::WPUser:
                                if( !( $authorisationContext["ignoreWpUserAuthAllowed"] && $requestContext["useSecondaryAuth"] ) )
                                {
                                    $rsvpId = $this->FindRsvpByUserId( $requestContext["wpUserId"] );
                                }
                                break;
                            case AuthMethod::NamePasscode:
                                $rsvpId = $this->FindRsvpByNamePasscode( $authenticationContext["firstName"], $authenticationContext["lastName"], $authenticationContext["passcode"] );
                                break;
                            case AuthMethod::Passcode:
                                $rsvpId = $this->FindRsvpByPasscode( $authenticationContext["passcode"] );
                                break;
                            case AuthMethod::Name:
                                $rsvpId = $this->FindRsvpByNamePasscode( $authenticationContext["firstName"], $authenticationContext["lastName"] );
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            else
            {
                return "You are not authorised to enter complete an RSVP, you may need to be logged in.";
            }

            if( $rsvpId )
            {
                $requestContext["rsvpId"] = $rsvpId;
            } 
            
            return null;
        }

        public function GetRsvpById( $rsvpId )
        {
            $rsvp = $this->config->GetDatabase()->get_row(
                $this->config->GetDatabase()->prepare(
                    "SELECT id
                    FROM ".$this->config->AttendeesTable()."
                    WHERE ID = %d ", $rsvpId ));

                return $rsvp;
        }

        private function RenderRequest( $requestContext, $authenticationContext, $authorisationContext )
        {
            if( $requestContext["rsvpId"] )
            {
                $rsvp = $this->GetRsvpById( $requestContext["rsvpId"] );
            }
                
            $requestAction = $requestContext["action"];
                
            switch ($requestAction) 
            {
            case SubmissionState::LandingPage:
            
            if ( $requestContext["rsvpStatus"] == RsvpStatus::Accepted || $requestContext["rsvpStatus"] == RsvpStatus::Declined ) {
                $requestAction = SubmissionState::AmendRsvp;
            } else if( $requestContext["rsvpStatus"] == RsvpStatus::NoResponse ) {
                $requestAction = SubmissionState::NewRsvp;
            } else {
                return $this->RenderLoginForm( $requestContext, $authorisationContext );
                break;
            }
            
            case SubmissionState::FindByNamePasscode:
                return "FindByNamePasscode";
                break;
            case SubmissionState::FindByPasscode:
                return "
                <input type=\"hidden\" name=\"rsvpStep\" value=\"1\" />
                <h1>Find by Passcode</h1><button name=\"rsvpStep\" value=\"2\">Search by Name</button>";
                break;
            case SubmissionState::FindByName:
                return "<h1>Find by Name</h1>";
                break;
            case SubmissionState::ProcesRsvp:
                break;
            case SubmissionState::NewRsvp:
                return "<h1>No responses lodged for this RSVP</h1>";
                break;
            case SubmissionState::AmendRsvp:
                return "<h1>Ammending an existing RSVP</h1>";
                break;
            case SubmissionState::AcknowledgeSubmission:
                break;
            case SubmissionState::RsvpNotFound:
                break;
            default:
                return "An error has occured";
                break;
            }
        }

        private function RenderLoginForm( $requestContext, $authorisationContext )
        {
            $authMethodForm = null;
            for ($i=0; $i < count( $authorisationContext["authMethodPriority"] ) && $authMethodForm == null; $i++) { 
                if( $authorisationContext["authMethodPriority"][$i] != AuthMethod::WPUser )
                {
                    $authMethodForm = $authorisationContext["authMethodPriority"];
                }
            }

            switch( $authMethodForm )
            {
            case AuthMethod::NamePasscode:
                return "
<h1>Find by Name and Passcode</h1>
<input type=\"text\" name=\"firstName\" placeholder=\"First Name\" />
<input type=\"text\" name=\"lastName\" placeholder=\"Last Name\" />
<input type=\"text\" name=\"passcode\" placeholder=\"Passcode\" />
<submit>Find my rsvp</submit>";
                break;
            case AuthMethod::Passcode:
            return "
<h1>Find by Passcode</h1>
<input type=\"text\" name=\"passcode\" placeholder=\"Passcode\" />
<submit>Find my rsvp</submit>";
                break;
            case AuthMethod::Name:
            return "
<h1>Find by Name</h1>
<input type=\"text\" name=\"firstName\" placeholder=\"First Name\" />
<input type=\"text\" name=\"lastName\" placeholder=\"Last Name\" />
<submit>Find my rsvp</submit>";
                break;
            default:
            return "
<h3>Unable to display form, you may need to be logged in to complete this action</h3><h2>tl;dl; Nothing to  see here, move along</h2>";
                break;
            }
        }

        public function RenderCustomCSS()
        {
            $customCss = get_option(Config::OPTION_RSVP_CSS_STYLING);

            if (!empty($customCss)) {
                $output = "<!-- RSVP Free Styling -->";
                $output .= "<style type=\"text/css\">" . $css . "</style>";

                echo $output;
            }
        }
    }
}

?>