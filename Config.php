<?php
namespace RSVPPlugin;


if ( !class_exists( 'Config' ) ) {
	
	class Config
	{
		function __construct( ) {	
		    global $wpdb;
			$this->database = $wpdb;
			$this->dbPrefix = $this->database->prefix;
		}
		//const RSVP_FRONTEND_TEXT_CHECK = "rsvp-pluginhere";
		const OPTION_GREETING = "rsvp_custom_greeting";
		const OPTION_THANKYOU = "rsvp_custom_thankyou";
		const OPTION_DEADLINE = "rsvp_deadline";
		const OPTION_OPENDATE = "rsvp_opendate";
		const OPTION_YES_VERBIAGE = "rsvp_yes_verbiage";
		const OPTION_NO_VERBIAGE = "rsvp_no_verbiage";
		const OPTION_KIDS_MEAL_VERBIAGE = "rsvp_kids_meal_verbiage";
		const OPTION_VEGGIE_MEAL_VERBIAGE = "rsvp_veggie_meal_verbiage";
		const OPTION_NOTE_VERBIAGE = "rsvp_note_verbiage";
		const OPTION_RSVP_HIDE_NOTE = "rsvp_hide_note_field";
		const OPTION_HIDE_VEGGIE = "rsvp_hide_veggie";
		const OPTION_HIDE_KIDS_MEAL = "rsvp_hide_kids_meal";
		const OPTION_HIDE_ADD_ADDITIONAL = "rsvp_hide_add_additional";
		const OPTION_NOTIFY_ON_RSVP = "rsvp_notify_when_rsvp";
		const OPTION_NOTIFY_EMAIL = "rsvp_notify_email_address";
		const OPTION_DEBUG_RSVP_QUERIES = "rsvp_debug_queries";
		const OPTION_WELCOME_TEXT = "rsvp_custom_welcome";
		const OPTION_RSVP_QUESTION = "rsvp_custom_question_text";
		const OPTION_RSVP_CUSTOM_YES_NO = "rsvp_custom_yes_no";

		const OPTION_FORM_AUTOSCROLL = "rsvp_dont_use_hash";
		const OPTION_CONFIG_ADMINISTRATOR_ROLE = "rsvp_administrator_role";
		const OPTION_CONFIG_ALLOW_USER_SEARCH = "rsvp_allow_user_search";
		const OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE = "rsvp_passcode_user_search";
		const OPTION_CONFIG_ALLOW_PASSCODE = "rsvp_passcode";
		const OPTION_CONFIG_ALLOW_UNAUTHENTICATED = "rsvp_allow_unauthenticated";
		const OPTION_CONFIG_PASSCODE_MIN_LENGTH = "rsvp_passcode_length";

		const OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE = "rsvp_add_additional_verbiage";
		const OPTION_RSVP_GUEST_EMAIL_CONFIRMATION = "rsvp_guest_email_confirmation";
		const OPTION_RSVP_NUM_ADDITIONAL_GUESTS = "rsvp_num_additional_guests";
		const OPTION_RSVP_HIDE_EMAIL_FIELD = "rsvp_hide_email_field";
		const OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM = "rsvp_disable_custom_from_email";
		const OPTION_RSVP_EMAIL_TEXT = "rsvp_email_text";
		const OPTION_RSVP_DELETE_DATA_ON_UNINSTALL = "rsvp_delete_data_on_uninstall";
		const OPTION_RSVP_CSS_STYLING = "rsvp_css_styling";

		CONST RSVP_CURRENT_DB_VERSION = "rsvp_db_version";
		
		const RSVP_SUPPORTED_DB_VERSION = "12";
		
		const QT_SHORT = "shortAnswer";
		const QT_MULTI = "multipleChoice";
		const QT_LONG = "longAnswer";
		const QT_DROP = "dropdown";
		const QT_RADIO = "radio";

		const RSVP_START_PARA = "<p class=\"rsvpParagraph\">";
		const RSVP_END_PARA = "</p>\r\n";
		const RSVP_START_CONTAINER = "<div id=\"rsvpPlugin\">\r\n";
		const RSVP_END_CONTAINER = "</div>\r\n";
		const RSVP_START_FORM_FIELD = "<div class=\"rsvpFormField\">\r\n";
		const RSVP_END_FORM_FIELD = "</div>\r\n";
		
		private $shortCode = "rsvp-pluginhere";
		private $dbPrefix;
		private $database;

		private $attendeesTable = "rsvp_attendees";
		private $associatedAttendeesTable = "rsvp_associated_attendees";
		private $questionsTable = "rsvp_custom_questions";
		private $questionTypesTable = "rsvp_question_types";
		private $attendeeAnswersTable = "rsvp_attendee_answers";
		private $questionAnswersTable = "rsvp_custom_question_answers";
		private $questionAttendeesTable = "rsvp_custom_question_attendees";

		private function GetNormalisedTableName( $tableName )
		{
			return $this->dbPrefix . $tableName;
		}

		public function GetDefaultCustomQuestionTypes()
		{
			return array(
				array("questionType" => "shortAnswer", "friendlyName" => "Short Answer"),
				array("questionType" => "multipleChoice", "friendlyName" => "Multiple Choice"),
				array("questionType" => "longAnswer", "friendlyName" => "Long Answer"),
				array("questionType" => "dropdown", "friendlyName" => "Drop Down"),
				array("questionType" => "radio", "friendlyName" => "Radio"),
			);
		}

		public function GetShortCode()
		{
			return $this->shortCode;
		}
		
		public function AttendeesTable()
		{
			return $this->GetNormalisedTableName( $this->attendeesTable );
		}
		
		public function AssociatedAttendeesTable()
		{
			return $this->GetNormalisedTableName(  $this->associatedAttendeesTable );
		}
		
		public function QuestionsTable()
		{
			return $this->GetNormalisedTableName(  $this->questionsTable );
		}
		
		public function QuestionTypesTable()
		{
			return $this->GetNormalisedTableName(  $this->questionTypesTable );
		}
		
		public function AttendeeAnswersTable()
		{
			return $this->GetNormalisedTableName(  $this->attendeeAnswersTable );
		}
		
		public function QuestionAnswersTable()
		{
			return $this->GetNormalisedTableName(  $this->questionAnswersTable );
		}
		
		public function QuestionAttendeesTable()
		{
			return $this->GetNormalisedTableName(  $this->questionAttendeesTable );
		}
		public function GetDatabase()
		{
			return $this->database;
		}

		public function RegisterOptions()
		{
			register_setting("rsvp-option-group", Config::OPTION_GREETING);
			register_setting("rsvp-option-group", Config::OPTION_THANKYOU);
			register_setting("rsvp-option-group", Config::OPTION_DEADLINE);
			register_setting("rsvp-option-group", Config::OPTION_OPENDATE);
			register_setting("rsvp-option-group", Config::OPTION_YES_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_NO_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_KIDS_MEAL_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_VEGGIE_MEAL_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_NOTE_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_HIDE_NOTE);
			register_setting("rsvp-option-group", Config::OPTION_HIDE_VEGGIE);
			register_setting("rsvp-option-group", Config::OPTION_HIDE_KIDS_MEAL);
			register_setting("rsvp-option-group", Config::OPTION_HIDE_ADD_ADDITIONAL);
			register_setting("rsvp-option-group", Config::OPTION_NOTIFY_ON_RSVP);
			register_setting("rsvp-option-group", Config::OPTION_NOTIFY_EMAIL);
			register_setting("rsvp-option-group", Config::OPTION_DEBUG_RSVP_QUERIES);
			register_setting("rsvp-option-group", Config::OPTION_WELCOME_TEXT);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_QUESTION);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_CUSTOM_YES_NO);
	
			register_setting("rsvp-option-group", Config::OPTION_FORM_AUTOSCROLL);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_ADMINISTRATOR_ROLE);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_ALLOW_USER_SEARCH);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_ALLOW_PASSCODE);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_ALLOW_UNAUTHENTICATED);
			register_setting("rsvp-option-group", Config::OPTION_CONFIG_PASSCODE_MIN_LENGTH);
	
			register_setting("rsvp-option-group", Config::OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_GUEST_EMAIL_CONFIRMATION);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_NUM_ADDITIONAL_GUESTS);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_HIDE_EMAIL_FIELD);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_EMAIL_TEXT);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_DELETE_DATA_ON_UNINSTALL);
			register_setting("rsvp-option-group", Config::OPTION_RSVP_CSS_STYLING);
	
			register_setting("rsvp-option-group", Config::RSVP_CURRENT_DB_VERSION);

			wp_register_script('jquery_table_sort', plugins_url('jquery.tablednd_0_5.js', __FILE__));
			wp_register_script('jquery_ui', "//ajax.microsoft.com/ajax/jquery.ui/1.8.5/jquery-ui.js");
			wp_register_style('jquery_ui_stylesheet', "//ajax.microsoft.com/ajax/jquery.ui/1.8.5/themes/redmond/jquery-ui.css");
		}
	}
}
?>