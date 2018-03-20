<?php
/**
 * @package rsvp
 * @author Swim or Die Software
 * @version 2.4.2
 */
/*
* Plugin Name: RSVP
* Text Domain: rsvp-plugin
* Plugin URI: http://wordpress.org/extend/plugins/rsvp/
* Description: This plugin allows guests to RSVP to an event.  It was made initially for weddings but could be used for other things.
* Author: Swim or Die Software
* Version: 2.4.2
* Author URI: http://www.swimordiesoftware.com
* License: GPL
*/
#
# INSTALLATION: see readme.txt
#
# USAGE: Once the RSVP plugin has been installed, you can set the custom text
#        via Settings -> RSVP Options in the  admin area.
#
#        To add, edit, delete and see rsvp status there will be a new RSVP admin
#        area just go there.
#
#        To allow people to rsvp create a new page and add "[rsvp]" to the text

namespace RSVPPlugin;
require_once("Config.php");
require_once("ClientRenderer.php");
require_once("RSVPAdmin.php");

if ( !class_exists( 'Plugin' ) ) {
	
	class Plugin
	{
        private $config;
		private $client;
		private $admin;

        function __construct( $pluginConfiguration ) {
            
            $this->config = $pluginConfiguration;
        }
		
		public function OnPageLoad()
		{
			
			/*
			
			add_action('admin_init', 'RSVPPlugin\Admin\RegisterSettings');
						
			
			*/

			add_action('init', [$this,'OnActivate']);
			
			register_activation_hook(__FILE__, [$this,'DatabaseSetup']);

			$this->admin = new RSVPAdmin( $this->config );
			
			$this->client = new ClientRenderer( $this->config );
		}

		public function OnActivate()
		{
			$this->config->RegisterOptions();

			wp_register_script('jquery_table_sort', plugins_url('jquery.tablednd_0_5.js', __FILE__));
			wp_register_script('jquery_ui', "//ajax.microsoft.com/ajax/jquery.ui/1.8.5/jquery-ui.js");
			wp_register_style('jquery_ui_stylesheet', "//ajax.microsoft.com/ajax/jquery.ui/1.8.5/themes/redmond/jquery-ui.css");

			wp_enqueue_script("jquery");
			wp_enqueue_script("jquery-ui-datepicker");
			wp_enqueue_script("jquery_table_sort");
			wp_enqueue_style('jquery_ui_stylesheet');
			wp_register_script('jquery_multi_select', plugins_url('multi-select/js/jquery.multi-select.js', __FILE__));
			wp_enqueue_script("jquery_multi_select");
			wp_register_style('jquery_multi_select_css', plugins_url("multi-select/css/multi-select.css", __FILE__));
			wp_enqueue_style('jquery_multi_select_css');
		
			wp_register_script('rsvp_admin', plugins_url('rsvp_plugin_admin.js', __FILE__));
			wp_enqueue_script("rsvp_admin");
			load_plugin_textdomain('rsvp-plugin', false, basename(dirname(__FILE__)) . '/languages/');
			wp_register_script('jquery_validate', "//ajax.aspnetcdn.com/ajax/jquery.validate/1.15.0/jquery.validate.min.js");
			wp_register_script('rsvp_plugin', plugins_url("rsvp_plugin.js", __FILE__));
			wp_localize_script(
					'rsvp_plugin',
					'rsvp_plugin_vars',
					array(
							'askEmail' => __("Please enter an email address that we can use to contact you about the extra guest.  We have to keep a pretty close eye on the number of attendees.  Thanks!", 'rsvp-plugin'),
							'customNote' => __("If you are adding additional RSVPs please enter your email address in case we have questions", 'rsvp-plugin'),
							'newAttending1LastName' => __("Please enter a last name", 'rsvp-plugin'),
							'newAttending1FirstName' => __("Please enter a first name", 'rsvp-plugin'),
							'newAttending2LastName' => __("Please enter a last name", 'rsvp-plugin'),
							'newAttending2FirstName' => __("Please enter a first name", 'rsvp-plugin'),
							'newAttending3LastName' => __("Please enter a last name", 'rsvp-plugin'),
							'newAttending3FirstName' => __("Please enter a first name", 'rsvp-plugin'),
							'attendeeFirstName' => __("Please enter a first name", 'rsvp-plugin'),
							'attendeeLastName' => __("Please enter a last name", 'rsvp-plugin'),
							'firstName' => __("Please enter your first name", 'rsvp-plugin'),
							'lastName' => __("Please enter your last name", 'rsvp-plugin'),
							'passcode' => __("Please enter your password", 'rsvp-plugin')
							)
					);
			wp_register_style('rsvp_css', plugins_url("rsvp_plugin.css", __FILE__));
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery_validate');
			wp_enqueue_script('rsvp_plugin');
			wp_enqueue_style("rsvp_css");
		}

		public function InstallPasscodeField( $db )
		{
			$table = $this->config->AttendeesTable();
    		$sql = "SHOW COLUMNS FROM `$table` LIKE 'passcode'";
    		if (!$db->get_results($sql)) {
        		$sql = "ALTER TABLE `$table` ADD `passcode` VARCHAR(50) NOT NULL DEFAULT '';";
        		$db->query($sql);
    		}
		}
		
		public function EnsureDefaultCustomQuestionTypes()
		{
			$defaultQuestionTypes = $this->config->GetDefaultCustomQuestionTypes();
			
			$questionTypes = $this->config->GetDatabase()->get_var( 
				$this->config->GetDatabase()->prepare("SELECT DISTINCT id FROM " . $this->QuestionTypesTable() )
			);

			for ($i=count($defaultQuestionTypes); $i >= 0; $i--) { 

				$defaultType = $defaultQuestionTypes["questionType"];

				if( in_array($defaultType["questionType"], $questionTypes["questionType"]) )
				{
					array_splice( $defaultQuestionTypes, i, 1 );
				}
			}

			foreach ($defaultQuestionTypes as $missingDefaultType ) {
				$this->config->GetDatabase()->insert( 
					$this->config->QuestionTypesTable(), array(
						"questionType" => $missingDefaultType['questionType'],
						"friendlyName" => $missingDefaultType['friendlyName']
					),
					array('%s', '%s')
				);
			}
		}
		
		private function DatabaseV1Upgrade( $db )
		{
			$table = $this->config->AttendeesTable();

			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE ".$table." (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`firstName` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
				`lastName` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
				`contactNumber` VARCHAR( 10 ) NOT NULL DEFAULT '',
				`rsvpDate` DATE NULL ,
				`rsvpStatus` ENUM( 'Yes', 'No', 'NoResponse' ) NOT NULL DEFAULT 'NoResponse',
				`note` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
				`kidsMeal` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N',
				`additionalAttendee` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N',
				`veggieMeal` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N', 
				`personalGreeting` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 
				);";
				$db->query($sql);
			}

			$table = $this->config->AssociatedAttendeesTable();

			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE ".$table." (
				`attendeeID` INT NOT NULL ,
				`associatedAttendeeID` INT NOT NULL
				);";
				$db->query($sql);
				$sql = "ALTER TABLE `".$table."` ADD INDEX ( `attendeeID` ) ";
				$db->query($sql);
				$sql = "ALTER TABLE `".$table."` ADD INDEX ( `associatedAttendeeID` )";
				$db->query($sql);

			}

			$table = $this->config->QuestionsTable();
			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = " CREATE TABLE $table (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`question` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
				`questionTypeID` INT NOT NULL, 
				`sortOrder` INT NOT NULL DEFAULT '99', 
				`permissionLevel` ENUM( 'public', 'private' ) NOT NULL DEFAULT 'public'
				);";
				$db->query($sql);
			}

			$table = $this->config->QuestionTypesTable();
			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = " CREATE TABLE $table (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`questionType` VARCHAR( 100 ) NOT NULL , 
				`friendlyName` VARCHAR(100) NOT NULL 
				);";
				$db->query($sql);
			} 
			

			$table = $this->config->QuestionAnswersTable();
			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE $table (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`questionID` INT NOT NULL, 
				`answer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
				);";
				$db->query($sql);
			}

			$table = $this->config->QuestionAttendeesTable();
			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE $table (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`questionID` INT NOT NULL, 
				`answer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, 
				`attendeeID` INT NOT NULL 
				);";
				$db->query($sql);
			}

			$table = $this->config->QuestionAttendeesTable();
			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE $table (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`questionID` INT NOT NULL ,
				`attendeeID` INT NOT NULL
				);";
				$db->query($sql);
			}

			// Fast forward to version 4
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "4");
		}
		
		private function DatabaseV2Upgrade( $db )
		{
			$table = $this->config->AttendeesTable();

   			$sql = "ALTER TABLE ".$table." ADD `personalGreeting` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
    		$db->query($sql);

			update_option($this->config::RSVP_CURRENT_DB_VERSION, "2");
		}

		private function DatabaseV3Upgrade( $db )
		{
			$table = $this->config->QuestionsTable();
    		$sql = "ALTER TABLE ".$table." ADD `sortOrder` INT NOT NULL DEFAULT '99';";
    		$db->query($sql);

			update_option($this->config::RSVP_CURRENT_DB_VERSION, "3");
		}

		private function DatabaseV4Upgrade( $db )
		{
			$table = $this->config->AssociatedAttendeesTable();

			if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
				$sql = "CREATE TABLE ".$table." (
				`attendeeID` INT NOT NULL ,
				`associatedAttendeeID` INT NOT NULL
				);";
				$db->query($sql);
				$sql = "ALTER TABLE `".$table."` ADD INDEX ( `attendeeID` ) ";
				$db->query($sql);
				$sql = "ALTER TABLE `".$table."` ADD INDEX ( `associatedAttendeeID` )";
				$db->query($sql);
			}

			$table = $this->config->AttendeesTable();

			$sql = "ALTER TABLE ".$table." ADD `personalGreeting` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;";
			$db->query($sql);

			update_option($this->config::RSVP_CURRENT_DB_VERSION, "4");
		}

		private function DatabaseV5Upgrade( $db )
		{
			$table = $this->config->QuestionsTable();
			$sql = "ALTER TABLE `$table` ADD `permissionLevel` ENUM( 'public', 'private' ) NOT NULL DEFAULT 'public';";
			$db->query($sql);
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "5");
		}

		private function DatabaseV6Upgrade( $db )
		{			
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "6");
		} 

		private function DatabaseV9Upgrade( $db )
		{
			$this->InstallPasscodeField( $db );
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "9");
		}

		private function DatabaseV11Upgrade( $db )
		{
			$table = $this->config->AttendeesTable();
			if ($db->get_var("SHOW COLUMNS FROM `$table` LIKE 'email'") != "email") {
				$sql = "ALTER TABLE ".$table." ADD `email` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
				$db->query($sql);
			}
			
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "11");
		}
		
		private function DatabaseV12Upgrade( $db )
		{
			$table = $this->config->AttendeesTable();
			if ($db->get_var("SHOW COLUMNS FROM `$table` LIKE 'contactPhone'") != "contactPhone") {
				$sql = "ALTER TABLE ".$table." ADD `contactPhone` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
				$db->query($sql);
			}
			if ($db->get_var("SHOW COLUMNS FROM `$table` LIKE 'wpUserId'") != "wpUserId") {
				$sql = "ALTER TABLE ".$table." ADD `wpUserId` BIGINT( 20 );";
				$db->query($sql);
			}
			update_option($this->config::RSVP_CURRENT_DB_VERSION, "12");
		}

		public function DatabaseSetup()
		{
    		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    		$installed_ver = get_option($this->config::RSVP_CURRENT_DB_VERSION);
			
			switch ($installed_ver) {
				case null;
					add_option($this->config::RSVP_CURRENT_DB_VERSION, "0");
				case '0':
				case '1':
					$this->DatabaseV1Upgrade($this->config->GetDatabase());
				case '2':
					$this->DatabaseV2Upgrade($this->config->GetDatabase());
    				
				case '3':
					$this->DatabaseV3Upgrade($this->config->GetDatabase());
				case '4':
					$this->DatabaseV4Upgrade($this->config->GetDatabase());
				case '5':
					$this->DatabaseV5Upgrade($this->config->GetDatabase());
				case '6':
					$this->DatabaseV6Upgrade($this->config->GetDatabase());
				case '7':
				case '8':
				case '9':
					$this->DatabaseV9Upgrade($this->config->GetDatabase());
				case '10':
				case  '11':
					$this->DatabaseV11Upgrade($this->config->GetDatabase());
				case  '11';
					$this->DatabaseV12Upgrade($this->config->GetDatabase());
					break;
				case  '12';
					break;
				default:
					throw new Exception("Unknown database version exception");
					break;
			}
			$this->EnsureDefaultCustomQuestionTypes();
		}
	}

    $rsvpPluginConfig = new Config();

	$rsvpPlugin = new Plugin($rsvpPluginConfig);
    $rsvpPlugin->OnPageLoad();
}
?>
