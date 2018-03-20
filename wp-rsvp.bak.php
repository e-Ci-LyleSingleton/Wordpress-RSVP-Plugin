<?php


$my_plugin_file = __FILE__;

if (isset($plugin)) {
    $my_plugin_file = $plugin;
} elseif (isset($mu_plugin)) {
    $my_plugin_file = $mu_plugin;
} elseif (isset($network_plugin)) {
    $my_plugin_file = $network_plugin;
}

define('RSVP_PLUGIN_FILE', $my_plugin_file);
define('RSVP_PLUGIN_PATH', WP_PLUGIN_DIR.'/'.basename(dirname($my_plugin_file)));
if ((isset($_GET['page']) && (strToLower($_GET['page']) == 'rsvp-admin-export')) ||
   (isset($_POST['rsvp-bulk-action']) && (strToLower($_POST['rsvp-bulk-action']) == "export"))) {
    add_action('init', 'rsvp_admin_export');
}

require_once('external-libs/wp-simple-nonce/wp-simple-nonce.php');
require_once("rsvp_frontend.inc.php");
/*
 * Description: Database setup for the rsvp plug-in.
 */
function rsvp_database_setup()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once("rsvp_db_setup.inc.php");
}

function rsvp_install_passcode_field()
{
    global $wpdb;
    $table = ATTENDEES_TABLE;
    $sql = "SHOW COLUMNS FROM `$table` LIKE 'passcode'";
    if (!$wpdb->get_results($sql)) {
        $sql = "ALTER TABLE `$table` ADD `passcode` VARCHAR(50) NOT NULL DEFAULT '';";
        $wpdb->query($sql);
    }
}

function rsvp_require_passcode()
{
    return ((get_option(OPTION_RSVP_PASSCODE) == "Y") || (get_option(OPTION_RSVP_OPEN_REGISTRATION) == "Y") || (get_option(OPTION_RSVP_ONLY_PASSCODE) == "Y"));
}

function rsvp_require_only_passcode_to_register()
{
    return (get_option(OPTION_RSVP_ONLY_PASSCODE) == "Y");
    
}

function rsvp_require_unique_passcode()
{
    return rsvp_require_only_passcode_to_register();
}

function rsvp_is_passcode_unique($passcode, $attendeeID)
{
    global $wpdb;

    $isUnique = false;

    $sql = $wpdb->prepare("SELECT * FROM ".ATTENDEES_TABLE." WHERE id <> %d AND passcode = %s", $attendeeID, $passcode);
    if (!$wpdb->get_results($sql)) {
        $isUnique = true;
    }

    return $isUnique;
}

/**
 * This generates a random 6 character passcode to be used for guests when the option is enabled.
 */
function rsvp_generate_passcode()
{
    $length = 6;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $passcode = "";

    for ($p = 0; $p < $length; $p++) {
        $passcode .= $characters[mt_rand(0, strlen($characters))];
    }

    return $passcode;
}

function rsvp_admin_guestlist_options()
{
    global $wpdb;
    if (rsvp_require_unique_passcode()) {
        $sql = "SELECT id, passcode FROM ".ATTENDEES_TABLE." a
				WHERE passcode <> '' AND (SELECT COUNT(*) FROM ".ATTENDEES_TABLE." WHERE passcode = a.passcode) > 1";
        $attendees = $wpdb->get_results($sql);
        foreach ($attendees as $a) {
            $wpdb->update(
            ATTENDEES_TABLE,
                      array("passcode" => rsvp_generate_passcode()),
                      array("id" => $a->id),
                      array("%s"),
                      array("%d")
        );
        }
    }

    if (rsvp_require_passcode()) {
        rsvp_install_passcode_field();

        $sql = "SELECT id, passcode FROM ".ATTENDEES_TABLE." WHERE passcode = ''";
        $attendees = $wpdb->get_results($sql);
        foreach ($attendees as $a) {
            $wpdb->update(
                ATTENDEES_TABLE,
                                        array("passcode" => rsvp_generate_passcode()),
                                        array("id" => $a->id),
                                        array("%s"),
                                        array("%d")
            );
        }
    } ?>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function() {
			jQuery("#rsvp_opendate").datepicker();
			jQuery("#rsvp_deadline").datepicker();
		});
	</script>
	<div class="wrap">
		<h2><?php echo __("RSVP Guestlist Options", 'rsvp-plugin'); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields('rsvp-option-group'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="rsvp_opendate"><?php echo __("RSVP Open Date:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_opendate" id="rsvp_opendate" value="<?php echo htmlspecialchars(get_option(OPTION_OPENDATE)); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_deadline"><?php echo __("RSVP Deadline:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_deadline" id="rsvp_deadline" value="<?php echo htmlspecialchars(get_option(OPTION_DEADLINE)); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_num_additional_guests"><?php echo __("Number of Additional Guests Allowed (default is three):", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_num_additional_guests" id="rsvp_num_additional_guests" value="<?php echo htmlspecialchars(get_option(OPTION_RSVP_NUM_ADDITIONAL_GUESTS)); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_custom_greeting"><?php echo __("Custom Greeting:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><textarea name="rsvp_custom_greeting" id="rsvp_custom_greeting" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_GREETING)); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_custom_welcome"><?php echo __("Custom Welcome:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><?php echo __("Default is: &quot;There are a few more questions we need to ask you if you could please fill them out below to finish up the RSVP process.&quot;", 'rsvp-plugin'); ?><br />
						<textarea name="rsvp_custom_welcome" id="rsvp_custom_welcome" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_WELCOME_TEXT)); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>"><?php echo __("Email Text: <br />Sent to guests in confirmation, at top of email", 'rsvp-plugin'); ?></label></th>
					<td align="left"><textarea name="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>" id="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_RSVP_EMAIL_TEXT)); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_custom_question_text"><?php echo __("RSVP Question Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><?php echo __("Default is: &quot;So, how about it?&quot;", 'rsvp-plugin'); ?><br />
						<input type="text" name="rsvp_custom_question_text" id="rsvp_custom_question_text"
						value="<?php echo htmlspecialchars(get_option(OPTION_RSVP_QUESTION)); ?>" size="65" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_yes_verbiage"><?php echo __("RSVP Yes Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_yes_verbiage" id="rsvp_yes_verbiage"
						value="<?php echo htmlspecialchars(get_option(OPTION_YES_VERBIAGE)); ?>" size="65" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_no_verbiage"><?php echo __("RSVP No Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_no_verbiage" id="rsvp_no_verbiage"
						value="<?php echo htmlspecialchars(get_option(OPTION_NO_VERBIAGE)); ?>" size="65" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_kids_meal_verbiage"><?php echo __("RSVP Kids Meal Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_kids_meal_verbiage" id="rsvp_kids_meal_verbiage"
						value="<?php echo htmlspecialchars(get_option(OPTION_KIDS_MEAL_VERBIAGE)); ?>" size="65" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_hide_kids_meal"><?php echo __("Hide Kids Meal Question:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="rsvp_hide_kids_meal" id="rsvp_hide_kids_meal"
						value="Y" <?php echo((get_option(OPTION_HIDE_KIDS_MEAL) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_veggie_meal_verbiage"><?php echo __("RSVP Vegetarian Meal Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_veggie_meal_verbiage" id="rsvp_veggie_meal_verbiage"
						value="<?php echo htmlspecialchars(get_option(OPTION_VEGGIE_MEAL_VERBIAGE)); ?>" size="65" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_hide_veggie"><?php echo __("Hide Vegetarian Meal Question:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="rsvp_hide_veggie" id="rsvp_hide_veggie"
						value="Y" <?php echo((get_option(OPTION_HIDE_VEGGIE) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_note_verbiage"><?php echo __("Note Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><textarea name="rsvp_note_verbiage" id="rsvp_note_verbiage" rows="3" cols="60"><?php
                        echo htmlspecialchars(get_option(OPTION_NOTE_VERBIAGE)); ?></textarea></td>
				</tr>
      			<tr valign="top">
        			<th scope="row"><label for="rsvp_hide_note_field"><?php echo __("Hide Note Field:", 'rsvp-plugin'); ?></label></th>
        			<td align="left"><input type="checkbox" name="rsvp_hide_note_field" id="rsvp_hide_note_field" value="Y"
          				<?php echo((get_option(RSVP_OPTION_HIDE_NOTE) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
      			</tr>
				<tr valign="top">
					<th scope="row"><label for="rsvp_custom_thankyou"><?php echo __("Custom Thank You:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><textarea name="rsvp_custom_thankyou" id="rsvp_custom_thankyou" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_THANKYOU)); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="rsvp_hide_add_additional"><?php echo __("Do not allow additional guests", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="rsvp_hide_add_additional" id="rsvp_hide_add_additional" value="Y"
						<?php echo((get_option(OPTION_HIDE_ADD_ADDITIONAL) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE; ?>"><?php echo __("Add Additional Verbiage:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><?php echo __("Default is: &quot;Did we slip up and forget to invite someone? If so, please add him or her here:&quot;", 'rsvp-plugin'); ?><br />
						<input type="text" name="<?php echo OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE; ?>" id="<?php echo OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE; ?>"
						value="<?php echo htmlspecialchars(get_option(OPTION_RSVP_ADD_ADDITIONAL_VERBIAGE)); ?>" size="65" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="rsvp_notify_when_rsvp"><?php echo __("Notify When Guest RSVPs", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="rsvp_notify_when_rsvp" id="rsvp_notify_when_rsvp" value="Y"
						<?php echo((get_option(OPTION_NOTIFY_ON_RSVP) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr>
					<th scope="row"><label for="rsvp_notify_email_address"><?php echo __("Email address to notify", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="text" name="rsvp_notify_email_address" id="rsvp_notify_email_address" value="<?php echo htmlspecialchars(get_option(OPTION_NOTIFY_EMAIL)); ?>"/></td>
				</tr>
      			<tr valign="top">
        			<th scope="row"><label for="rsvp_guest_email_confirmation"><?php echo __("Send email to main guest when they RSVP", 'rsvp-plugin'); ?></label></th>
        			<td align="left"><input type="checkbox" name="rsvp_guest_email_confirmation" id="rsvp_guest_email_confirmation" value="Y"
          				<?php echo((get_option(OPTION_RSVP_GUEST_EMAIL_CONFIRMATION) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
      			</tr>
				<tr>
					<th scope="ropw"><label for="<?php echo OPTION_RSVP_PASSCODE; ?>"><?php echo __("Require a Passcode to RSVP:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_PASSCODE; ?>" id="<?php echo OPTION_RSVP_PASSCODE; ?>" value="Y"
						 <?php echo((get_option(OPTION_RSVP_PASSCODE) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr>
					<th scope="ropw"><label for="<?php echo OPTION_RSVP_ONLY_PASSCODE; ?>"><?php echo __("Require only a Passcode to RSVP<br />(requires that passcodes are unique):", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_ONLY_PASSCODE; ?>" id="<?php echo OPTION_RSVP_ONLY_PASSCODE; ?>" value="Y"
						 <?php echo((get_option(OPTION_RSVP_ONLY_PASSCODE) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
      			<tr valign="top">
        			<th scope="row"><label for="<?php echo OPTION_RSVP_OPEN_REGISTRATION; ?>"><?php echo __("Allow Open Registration (note - this will force passcodes for attendees):", 'rsvp-plugin'); ?></label></th>
        			<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_OPEN_REGISTRATION; ?>" id="<?php echo OPTION_RSVP_OPEN_REGISTRATION; ?>" value="Y"
           				<?php echo((get_option(OPTION_RSVP_OPEN_REGISTRATION) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
      			</tr>
      			<tr valign="top">
        			<th scope="row"><label for="<?php echo OPTION_RSVP_DONT_USE_HASH; ?>"><?php echo __("Do not scroll page to the top of the RSVP form:", 'rsvp-plugin'); ?></label></th>
        			<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_DONT_USE_HASH; ?>" id="<?php echo OPTION_RSVP_DONT_USE_HASH; ?>" value="Y"
           				<?php echo((get_option(OPTION_RSVP_DONT_USE_HASH) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
      			</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo OPTION_RSVP_HIDE_EMAIL_FIELD; ?>"><?php echo __("Hide email field on rsvp form:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_HIDE_EMAIL_FIELD; ?>" id="<?php echo OPTION_RSVP_HIDE_EMAIL_FIELD; ?>"
						value="Y" <?php echo((get_option(OPTION_RSVP_HIDE_EMAIL_FIELD) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM; ?>"><?php echo __("Do not use the specified notification email as the from email<br /> (if you are not receiving email notifications try this):", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM; ?>" id="<?php echo OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM; ?>"
						value="Y" <?php echo((get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo OPTION_RSVP_DISABLE_USER_SEARCH; ?>"><?php echo __("Disable searching for a user when no user is found:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo OPTION_RSVP_DISABLE_USER_SEARCH; ?>" id="<?php echo OPTION_RSVP_DISABLE_USER_SEARCH; ?>"
						value="Y" <?php echo((get_option(OPTION_RSVP_DISABLE_USER_SEARCH) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo RSVP_OPTION_DELETE_DATA_ON_UNINSTALL; ?>"><?php echo __("Delete all data on uninstall:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><input type="checkbox" name="<?php echo RSVP_OPTION_DELETE_DATA_ON_UNINSTALL; ?>" id="<?php echo RSVP_OPTION_DELETE_DATA_ON_UNINSTALL; ?>"
						value="Y" <?php echo((get_option(RSVP_OPTION_DELETE_DATA_ON_UNINSTALL) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="<?php echo RSVP_OPTION_CSS_STYLING; ?>"><?php echo __("Custom Styling:", 'rsvp-plugin'); ?></label></th>
					<td align="left"><textarea name="<?php echo RSVP_OPTION_CSS_STYLING; ?>" id="<?php echo RSVP_OPTION_CSS_STYLING; ?>" rows="5" cols="60"><?php echo htmlspecialchars(get_option(RSVP_OPTION_CSS_STYLING)); ?></textarea>
						<br />
						<span class="description"><?php _e('Add custom CSS for the RSVP plugin. More details <a href="https://www.rsvpproplugin.com/knowledge-base/customizing-the-rsvp-pro-front-end/">here</a>', 'rsvp-plugin'); ?></span>
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="update" />
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php echo __('Save Changes', 'rsvp-plugin'); ?>" />
			</p>
		</form>
	</div>
<?php
}

/*
function rsvp_admin_export()
{
    global $wpdb;

    $customLinkBase = "";

    // Get page associated with the page to build out prefill link.
    $query = new WP_Query('s=rsvp-pluginhere');
    if ($query->have_posts()) {
        $query->the_post();
        $customLinkBase = get_permalink();
        if (strpos($customLinkBase, "?") !== false) {
            $customLinkBase .= "&";
        } else {
            $customLinkBase .= "?";
        }

        if (rsvp_require_only_passcode_to_register()) {
            $customLinkBase .= "passcode=%s";
        } else {
            $customLinkBase .= "firstName=%s&lastName=%s";
            if (rsvp_require_passcode()) {
                $customLinkBase .= "&passcode=%s";
            }
        }
    }

    wp_reset_postdata();

    $sql = "SELECT id, firstName, lastName, email, rsvpStatus, note, kidsMeal, additionalAttendee, veggieMeal, passcode
						FROM ".ATTENDEES_TABLE;

    $orderBy = " lastName, firstName";
    if (isset($_POST['sortValue'])) {
        if (strToLower($_POST['sortValue']) == "rsvpstatus") {
            $orderBy = " rsvpStatus ".((strtolower($_POST['exportSortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
        } elseif (strToLower($_POST['sortValue']) == "attendee") {
            $direction = ((strtolower($_POST['exportSortDirection']) == "desc") ? "DESC" : "ASC");
            $orderBy = " lastName $direction, firstName $direction";
        } elseif (strToLower($_POST['sortValue']) == "kidsmeal") {
            $orderBy = " kidsMeal ".((strtolower($_POST['exportSortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
        } elseif (strToLower($_POST['sortValue']) == "additional") {
            $orderBy = " additionalAttendee ".((strtolower($_POST['exportSortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
        } elseif (strToLower($_POST['sortValue']) == "vegetarian") {
            $orderBy = " veggieMeal ".((strtolower($_POST['exportSortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
        }
    }
    $sql .= " ORDER BY ".$orderBy;
    $attendees = $wpdb->get_results($sql);
    $csv = "\"".__("First Name", 'rsvp-plugin')."\",\"".__("Last Name", 'rsvp-plugin')."\",\"".__("Email", 'rsvp-plugin')."\",\"".__("RSVP Status", 'rsvp-plugin')."\",";

    if (get_option(OPTION_HIDE_KIDS_MEAL) != "Y") {
        $csv .= "\"".__("Kids Meal", 'rsvp-plugin')."\",";
    }

    $csv .= "\"".__("Associated Attendees", 'rsvp-plugin')."\",";

    if (get_option(OPTION_HIDE_VEGGIE) != "Y") {
        $csv .= "\"".__("Vegetarian", 'rsvp-plugin')."\",";
    }
    if (rsvp_require_passcode()) {
        $csv .= "\"".__("Passcode", 'rsvp-plugin')."\",";
    }
    $csv .= "\"".__("Note", 'rsvp-plugin')."\"";

    $qRs = $wpdb->get_results("SELECT id, question, permissionLevel FROM ".QUESTIONS_TABLE." ORDER BY sortOrder, id");
    if (count($qRs) > 0) {
        foreach ($qRs as $q) {
            $csv .= ",\"".stripslashes($q->question)."\"";
            if ($q->permissionLevel == "private") {
                $csv .= ",\"pq_".$q->id."\"";
            }
        }
    }

    $csv .= ",\"".__("Additional Attendee", 'rsvp-plugin')."\"";
    $csv .= ",\"".__("pre-fill URL", 'rsvp-plugin')."\"";

    $csv .= "\r\n";
    foreach ($attendees as $a) {
        $fName = stripslashes($a->firstName);
        $fName = rsvp_handle_text_encoding( $fName );
        $lName = stripslashes($a->lastName);
        $lName = rsvp_handle_text_encoding( $lName );
        $csv .= "\"".$fName."\",\"".$lName."\",\"".stripslashes($a->email)."\",\"".($a->rsvpStatus)."\",";

        if (get_option(OPTION_HIDE_KIDS_MEAL) != "Y") {
            $csv .= "\"".(($a->kidsMeal == "Y") ? "Y" : "N")."\",";
        }

        $csv .= "\"";
        $sql = "SELECT firstName, lastName FROM ".ATTENDEES_TABLE."
			 	WHERE id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d)
					OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)";

        $associations = $wpdb->get_results($wpdb->prepare($sql, $a->id, $a->id));
        foreach ($associations as $assc) {
            $csv .= rsvp_handle_text_encoding( trim( stripslashes( $assc->firstName." ".$assc->lastName ) ) )."\r\n";
        }
        $csv .= "\",";

        if (get_option(OPTION_HIDE_VEGGIE) != "Y") {
            $csv .= "\"".(($a->veggieMeal == "Y") ? "Y" : "N")."\",";
        }

        if (rsvp_require_passcode()) {
            $csv .= "\"".(($a->passcode))."\",";
        }

        $csv .= "\"".(str_replace("\"", "\"\"", stripslashes($a->note)))."\"";

        $qRs = $wpdb->get_results($wpdb->prepare("SELECT q.id, question, permissionLevel, qat.questionID AS hasAccess,
							(SELECT GROUP_CONCAT(answer) FROM ".ATTENDEE_ANSWERS." WHERE questionID = q.id AND attendeeID = %d) AS answer
							FROM ".QUESTIONS_TABLE." q
							LEFT JOIN ".QUESTION_ATTENDEES_TABLE." qat ON qat.questionID = q.id AND qat.attendeeID = %d
							ORDER BY sortOrder, q.id", $a->id, $a->id));
        if (count($qRs) > 0) {
            foreach ($qRs as $q) {
                if ($q->answer != "") {
                    $csv .= ",\"".stripslashes($q->answer)."\"";
                } else {
                    $csv .= ",\"\"";
                }

                if ($q->permissionLevel == "private") {
                    $csv .= ",\"".(($q->hasAccess != "") ? "Y" : "N")."\"";
                }
            }
        }

        $csv .= ",\"".(($a->additionalAttendee == "Y") ? "Y" : "N")."\"";
        if (empty($customLinkBase)) {
            $csv .= ",\"\"";
        } else {
            if (rsvp_require_only_passcode_to_register()) {
                $csv .= ",\"".sprintf($customLinkBase, urlencode(stripslashes($a->passcode)))."\"";
            } elseif (rsvp_require_passcode()) {
                $csv .= ",\"".sprintf($customLinkBase, urlencode(stripslashes($a->firstName)), urlencode(stripslashes($a->lastName)), urlencode(stripslashes($a->passcode)))."\"";
            } else {
                $csv .= ",\"".sprintf($customLinkBase, urlencode(stripslashes($a->firstName)), urlencode(stripslashes($a->lastName)))."\"";
            }
        }
        $csv .= "\r\n";
    }
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
        // IE Bug in download name workaround
        ini_set('zlib.output_compression', 'Off');
    }
    header('Content-Description: RSVP Export');
    header("Content-Type: application/vnd.ms-excel", true);
    header('Content-Disposition: attachment; filename="rsvpEntries.csv"');
    echo $csv;
    exit();
}

function rsvp_admin_import()
{
    global $wpdb;
    if (count($_FILES) > 0) {
        check_admin_referer('rsvp-import');
        require('spreadsheet-reader/php-excel-reader/excel_reader2.php');
        require('spreadsheet-reader/SpreadsheetReader.php');

        $data = new SpreadsheetReader($_FILES['importFile']['tmp_name'], $_FILES['importFile']['name']);
        $numCols = count($data->current());

        if ($numCols >= 2) {
            $headerRow = array();
            $count = 0;
            $i = 0;

            foreach ($data as $row) {
                if ($i > 0) {
                    $fName = trim($row[0]);
                    $fName = rsvp_handle_text_encoding( $fName );

                    $lName = trim($row[1]);
                    $lName = rsvp_handle_text_encoding( $lName );
                    $email = trim($row[2]);
                    $rsvpStatus = "noresponse";
                    if (isset($row[3])) {
                        $tmpStatus = strtolower($row[3]);
                        if (($tmpStatus == "yes") || ($tmpStatus == "no")) {
                            $rsvpStatus = $tmpStatus;
                        }
                    }
                    $kidsMeal = "N";
                    $vegetarian = "N";
                    if (isset($row[4]) && (strtolower($row[4]) == "Y")) {
                        $kidsMeal = "Y";
                    }

                    if (isset($row[6]) && (strtolower($row[6]) == "Y")) {
                        $vegetarian = "Y";
                    }

                    $personalGreeting = (isset($row[8])) ? $personalGreeting = $row[8] : "";
                    $passcode = (isset($row[7])) ? $row[7] : "";
                    if (rsvp_require_unique_passcode() && !rsvp_is_passcode_unique($passcode, 0)) {
                        $passcode = rsvp_generate_passcode();
                    }

                    if (!empty($fName) && !empty($lName)) {
                        $sql = "SELECT id, email, passcode FROM ".ATTENDEES_TABLE."
					 		WHERE firstName = %s AND lastName = %s ";
                        $res = $wpdb->get_results($wpdb->prepare($sql, $fName, $lName));
                        if (count($res) == 0) {
                            $wpdb->insert(
                                ATTENDEES_TABLE,
                                array("firstName"		=> $fName,
                                    "lastName"			=> $lName,
                                    "email"				=> $email,
                                    "personalGreeting"	=> $personalGreeting,
                                    "kidsMeal"			=> $kidsMeal,
                                    "veggieMeal"		=> $vegetarian,
                                    "rsvpStatus"		=> $rsvpStatus,
                                    "passcode"			=> $passcode),
                                array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
                            );
                            $count++;
                        } elseif (empty($res->email) && empty($res->passcode)) {
                            // More than likely the attendee was inserted via an
                            // associated attendee and we will want to update this record...
                            $wpdb->update(
                                ATTENDEES_TABLE,
                                array("email"			=> $email,
                                    "personalGreeting" 	=> $personalGreeting,
                                    "passcode"         	=> $passcode,
                                    "rsvpStatus"       	=> $rsvpStatus),
                                array("id" => $res[0]->id),
                                array('%s', '%s', '%s', '%s'),
                                array('%d')
                            );
                        }

                        if ($numCols >= 4) {
                            // Get the user's id
                            $sql = "SELECT id FROM ".ATTENDEES_TABLE."
							 	WHERE firstName = %s AND lastName = %s ";
                            $res = $wpdb->get_results($wpdb->prepare($sql, $fName, $lName));
                            if ((count($res) > 0) && isset($row[5])) {
                                $userId = $res[0]->id;

                                // Deal with the assocaited users...
                                $associatedUsers = explode(",", trim($row[5]));
                                if (is_array($associatedUsers)) {
                                    foreach ($associatedUsers as $au) {
                                        $user = explode(" ", trim($au), 2);
                                        // Three cases, they didn't enter in all of the information, user exists or doesn't.
                                        // If user exists associate the two users
                                        // If user does not exist add the user and then associate the two
                                        if (is_array($user) && (count($user) == 2)) {
                                            $sql = "SELECT id FROM ".ATTENDEES_TABLE."
											 	WHERE firstName = %s AND lastName = %s ";
                                            $userRes = $wpdb->get_results($wpdb->prepare(
                                                $sql,
                                                rsvp_handle_text_encoding( trim($user[0]) ),
                                                rsvp_handle_text_encoding( trim($user[1]) )
                                            ));
                                            if (count($userRes) > 0) {
                                                $newUserId = $userRes[0]->id;
                                            } else {
                                                // Insert them and then we can associate them...
                                                $wpdb->insert(
                                                    ATTENDEES_TABLE,
                                                    array(
                                                    "firstName" => rsvp_handle_text_encoding( trim($user[0]) ),
                                                    "lastName" => rsvp_handle_text_encoding( trim($user[1]) ) 
                                                	),
                                                    array('%s', '%s')
                                                );
                                                $newUserId = $wpdb->insert_id;
                                                $count++;
                                            }

                                            $wpdb->insert(
                                                ASSOCIATED_ATTENDEES_TABLE,
                                                array("attendeeID" => $newUserId,
                                                		"associatedAttendeeID" => $userId),
                                                array("%d", "%d")
                                            );

                                            $wpdb->insert(
                                                ASSOCIATED_ATTENDEES_TABLE,
                                                array("attendeeID" => $userId,
                                                    "associatedAttendeeID" => $newUserId),
                                                array("%d", "%d")
                                            );
                                        }
                                    } // foreach($associatedUsers...
                                } // if(is_array($associated...
                            } // if((count($res) > 0...
                        } // if check for associated attendees

                        if ($numCols >= 9) {
                            $private_questions = array();
                            for ($qid = 9; $qid <= $numCols; $qid++) {
                                $pqid = str_replace("pq_", "", $headerRow[$qid]);
                                if (is_numeric($pqid)) {
                                    $private_questions[$qid] = $pqid;
                                }
                            } // for($qid = 6...

                            if (count($private_questions) > 0) {
                                // Get the user's id
                                $sql = "SELECT id FROM ".ATTENDEES_TABLE." WHERE firstName = %s AND lastName = %s ";
                                $res = $wpdb->get_results($wpdb->prepare($sql, $fName, $lName));
                                if (count($res) > 0) {
                                    $userId = $res[0]->id;
                                    foreach ($private_questions as $key => $val) {
                                        if (strToUpper($row[$key]) == "Y") {
                                            $wpdb->insert(
                                                QUESTION_ATTENDEES_TABLE,
                                                array("attendeeID" => $userId,
                                                "questionID" => $val),
                                                  array("%d", "%d")
                                            );
                                        }
                                    }
                                }
                            } // if(count($priv...))
                        } // if($numCols > = 9
                    } // if(!empty($fName) && !empty($lName))
                } else {
                    $headerRow = $row;
                }
                $i++;
            } // foreach $data as $row?>
		<p><strong><?php echo $count; ?></strong> <?php echo __("total records were imported", 'rsvp-plugin'); ?>.</p>
		<p><?php echo __("Continue to the RSVP", 'rsvp-plugin'); ?> <a href="admin.php?page=rsvp-top-level"><?php echo __("list", 'rsvp-plugin'); ?></a></p>
		<?php
        }
    } else {
        ?>
		<form name="rsvp_import" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('rsvp-import'); ?>
			<p><?php echo __("Select a file in the following file format: XLS, XLSX, CSV and ODS. It has to have the following layout:", 'rsvp-plugin'); ?><br />
			<strong><?php echo __("First Name", 'rsvp-plugin'); ?></strong> | <strong><?php echo __("Last Name", 'rsvp-plugin'); ?></strong> | <strong><?php echo __("Email", 'rsvp-plugin'); ?></strong> |
			<strong><?php echo __("RSVP Status", 'rsvp-plugin'); ?></strong> | <strong><?php echo __("Kids Meal", 'rsvp-plugin'); ?></strong> |
    <strong><?php echo __("Associated Attendees", 'rsvp-plugin'); ?>*</strong> | <strong><?php echo __("Vegetarian", 'rsvp-plugin'); ?></strong> | <strong><?php echo __("Passcode", 'rsvp-plugin'); ?></strong> |
			<strong><?php echo __("Note", 'rsvp-plugin'); ?></strong> | <strong><?php echo __("Private Question Association", 'rsvp-plugin'); ?>**</strong>
			</p>
			<p>
			* <?php echo __("associated attendees should be separated by a comma it is assumed that the first space encountered will separate the first and last name.", 'rsvp-plugin'); ?>
			</p>
    <p>
      ** <?php echo __("This can be multiple columns each column is associated with one of the following private questions. If you wish
      to have the guest associated with the question put a &quot;Y&quot; in the column otherwise put whatever else you want. The header name will be the &quot;private import key&quot; which is also listed below. It has the format of pq_* where * is a number.", 'rsvp-plugin'); ?>
      <ul>
      <?php
      $questions = $wpdb->get_results("SELECT id, question FROM ".QUESTIONS_TABLE." WHERE permissionLevel = 'private'");
        foreach ($questions as $q) {
            ?>
        <li><?php echo htmlspecialchars(stripslashes($q->question)); ?> - pq_<?php echo $q->id; ?></li>
      <?php
        } ?>
      </ul>
    </p>
			<p><?php echo __("A header row is always expected.", 'rsvp-plugin'); ?></p>
			<p><input type="file" name="importFile" id="importFile" /></p>
			<p><input type="submit" value="Import File" name="goRsvp" /></p>
		</form>
	<?php
    }
}

*/

function rsvp_admin_questions()
{
    global $wpdb;

    if ((count($_POST) > 0) && ($_POST['rsvp-bulk-action'] == "delete") && (is_array($_POST['q']) && (count($_POST['q']) > 0))) {
        foreach ($_POST['q'] as $q) {
            if (is_numeric($q) && ($q > 0)) {
                $wpdb->query($wpdb->prepare("DELETE FROM ".QUESTIONS_TABLE." WHERE id = %d", $q));
                $wpdb->query($wpdb->prepare("DELETE FROM ".ATTENDEE_ANSWERS." WHERE questionID = %d", $q));
            }
        }
    } elseif ((count($_POST) > 0) && ($_POST['rsvp-bulk-action'] == "saveSortOrder")) {
        $sql = "SELECT id FROM ".QUESTIONS_TABLE;
        $sortQs = $wpdb->get_results($sql);
        foreach ($sortQs as $q) {
            if (is_numeric($_POST['sortOrder'.$q->id]) && ($_POST['sortOrder'.$q->id] >= 0)) {
                $wpdb->update(
                    QUESTIONS_TABLE,
                                            array("sortOrder" => $_POST['sortOrder'.$q->id]),
                                            array("id" => $q->id),
                                            array("%d"),
                                            array("%d")
                );
            }
        }
    }

    $sql = "SELECT id, question, sortOrder, permissionLevel FROM ".QUESTIONS_TABLE." ORDER BY sortOrder ASC";
    $customQs = $wpdb->get_results($sql); ?>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function() {
			jQuery("#cb").click(function() {
				if(jQuery("#cb").attr("checked")) {
					jQuery("input[name='q[]']").attr("checked", "checked");
				} else {
					jQuery("input[name='q[]']").removeAttr("checked");
				}
			});

			jQuery("#customQuestions").tableDnD({
				onDrop: function(table, row) {
					var rows = table.tBodies[0].rows;
        for (var i=0; i<rows.length; i++) {
            jQuery("#sortOrder" + rows[i].id).val(i);
        }

				}
			});
		});
	</script>
	<div class="wrap">
		<div id="icon-edit" class="icon32"><br /></div>
		<h2><?php echo __("List of current custom questions", 'rsvp-plugin'); ?></h2>
		<form method="post" id="rsvp-form" enctype="multipart/form-data">
			<input type="hidden" id="rsvp-bulk-action" name="rsvp-bulk-action" />
			<div class="tablenav">
				<div class="alignleft actions">
					<select id="rsvp-action-top" name="action">
						<option value="" selected="selected"><?php _e('Bulk Actions', 'rsvp-plugin'); ?></option>
						<option value="delete"><?php _e('Delete', 'rsvp-plugin'); ?></option>
					</select>
					<input type="submit" value="<?php _e('Apply', 'rsvp'); ?>" name="doaction" id="doaction" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = document.getElementById('rsvp-action-top').value;" />
					<input type="submit" value="<?php _e('Save Sort Order', 'rsvp'); ?>" name="saveSortButton" id="saveSortButton" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = 'saveSortOrder';" />
				</div>
				<div class="clear"></div>
			</div>
		<table class="widefat post fixed" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" id="cb" /></th>
					<th scope="col" id="questionCol" class="manage-column column-title" style=""><?php echo __("Question", 'rsvp-plugin'); ?></th>
        <th scope="col" class="manage-column column-title"><?php echo __("Private Import Key", 'rsvp-plugin'); ?></th>
				</tr>
			</thead>
		</table>
		<div style="overflow: auto;height: 450px;">
			<table class="widefat post fixed" cellspacing="0" id="customQuestions">
			<?php
                $i = 0;
    foreach ($customQs as $q) {
        ?>
					<tr class="<?php echo(($i % 2 == 0) ? "alternate" : ""); ?> author-self" id="<?php echo $q->id; ?>">
						<th scope="row" class="check-column"><input type="checkbox" name="q[]" value="<?php echo $q->id; ?>" /></th>
						<td>
							<a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-custom-question&amp;id=<?php echo $q->id; ?>"><?php echo htmlspecialchars(stripslashes($q->question)); ?></a>
							<input type="hidden" name="sortOrder<?php echo $q->id; ?>" id="sortOrder<?php echo $q->id; ?>" value="<?php echo $q->sortOrder; ?>" />
						</td>
          <td><?php
            if ($q->permissionLevel == "private") {
                ?>
              pq_<?php echo $q->id; ?>
          <?php
            } ?></td>
					</tr>
				<?php
                    $i++;
    } ?>
			</table>
		</div>
		</form>
	</div>
<?php
}

function rsvp_get_question_with_answer_type_ids()
{
    global $wpdb;

    $ids = array();
    $sql = "SELECT id FROM ".QUESTION_TYPE_TABLE."
			WHERE questionType IN ('".QT_MULTI."', '".QT_DROP."', '".QT_RADIO."')";
    $results = $wpdb->get_results($sql);
    foreach ($results as $r) {
        $ids[] = (int)$r->id;
    }

    return $ids;
}


function rsvp_admin_custom_question()
{
    global $wpdb;

    $answerQuestionTypes = rsvp_get_question_with_answer_type_ids();

    rsvp_populate_custom_question_types();

    if ((count($_POST) > 0) && !empty($_POST['question']) && is_numeric($_POST['questionTypeID'])) {
        check_admin_referer('rsvp_add_custom_question');
        if (isset($_POST['questionId']) && is_numeric($_POST['questionId']) && ($_POST['questionId'] > 0)) {
            $wpdb->update(
                QUESTIONS_TABLE,
                                        array("question" => trim($_POST['question']),
                                              "questionTypeID" => trim($_POST['questionTypeID']),
                                                    "permissionLevel" => ((trim($_POST['permissionLevel']) == "private") ? "private" : "public")),
                                        array("id" => $_POST['questionId']),
                                        array("%s", "%d", "%s"),
                                        array("%d")
            );
            $questionId = $_POST['questionId'];

            $answers = $wpdb->get_results($wpdb->prepare("SELECT id FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $questionId));
            if (count($answers) > 0) {
                foreach ($answers as $a) {
                    if (isset($_POST['deleteAnswer'.$a->id]) && (strToUpper($_POST['deleteAnswer'.$a->id]) == "Y")) {
                        $wpdb->query($wpdb->prepare("DELETE FROM ".QUESTION_ANSWERS_TABLE." WHERE id = %d", $a->id));
                    } elseif (isset($_POST['answer'.$a->id]) && !empty($_POST['answer'.$a->id])) {
                        $wpdb->update(
                            QUESTION_ANSWERS_TABLE,
                                                  array("answer" => trim($_POST['answer'.$a->id])),
                                                  array("id"=>$a->id),
                                                  array("%s"),
                                                  array("%d")
                        );
                    }
                }
            }
        } else {
            $wpdb->insert(
                QUESTIONS_TABLE,
                array("question" => trim($_POST['question']),
                                                 "questionTypeID" => trim($_POST['questionTypeID']),
                                                                                     "permissionLevel" => ((trim($_POST['permissionLevel']) == "private") ? "private" : "public")),
                                           array('%s', '%d', '%s')
            );
            $questionId = $wpdb->insert_id;
        }

        if (isset($_POST['numNewAnswers']) && is_numeric($_POST['numNewAnswers']) &&
           in_array($_POST['questionTypeID'], $answerQuestionTypes)) {
            for ($i = 0; $i < $_POST['numNewAnswers']; $i++) {
                if (isset($_POST['newAnswer'.$i]) && !empty($_POST['newAnswer'.$i])) {
                    $wpdb->insert(QUESTION_ANSWERS_TABLE, array("questionID"=>$questionId, "answer"=>$_POST['newAnswer'.$i]));
                }
            }
        }

        if (strToLower(trim($_POST['permissionLevel'])) == "private") {
            $wpdb->query($wpdb->prepare("DELETE FROM ".QUESTION_ATTENDEES_TABLE." WHERE questionID = %d", $questionId));
            if (isset($_POST['attendees']) && is_array($_POST['attendees'])) {
                foreach ($_POST['attendees'] as $aid) {
                    if (is_numeric($aid) && ($aid > 0)) {
                        $wpdb->insert(QUESTION_ATTENDEES_TABLE, array("attendeeID"=>$aid, "questionID"=>$questionId), array("%d", "%d"));
                    }
                }
            }
        } ?>
		<p><?php echo __("Custom Question saved", 'rsvp-plugin'); ?></p>
		<p>
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-questions"><?php echo __("Continue to Question List", 'rsvp-plugin'); ?></a> |
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-custom-question"><?php echo __("Add another Question", 'rsvp-plugin'); ?></a>
		</p>
	<?php
    } else {
        $questionTypeId = 0;
        $question = "";
        $isNew = true;
        $questionId = 0;
        $permissionLevel = "public";
        $savedAttendees = array();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $qRs = $wpdb->get_results($wpdb->prepare("SELECT id, question, questionTypeID, permissionLevel FROM ".QUESTIONS_TABLE." WHERE id = %d", $_GET['id']));
            if (count($qRs) > 0) {
                $isNew = false;
                $questionId = $qRs[0]->id;
                $question = stripslashes($qRs[0]->question);
                $permissionLevel = stripslashes($qRs[0]->permissionLevel);
                $questionTypeId = $qRs[0]->questionTypeID;

                if ($permissionLevel == "private") {
                    $aRs = $wpdb->get_results($wpdb->prepare("SELECT attendeeID FROM ".QUESTION_ATTENDEES_TABLE." WHERE questionID = %d", $questionId));
                    if (count($aRs) > 0) {
                        foreach ($aRs as $a) {
                            $savedAttendees[] = $a->attendeeID;
                        }
                    }
                }
            }
        }

        $sql = "SELECT id, questionType, friendlyName FROM ".QUESTION_TYPE_TABLE;
        $questionTypes = $wpdb->get_results($sql); ?>
			<script type="text/javascript">
				var questionTypeId = [<?php
                    foreach ($answerQuestionTypes as $aqt) {
                        echo "\"".$aqt."\",";
                    } ?>];
				function addAnswer(counterElement) {
					var currAnswer = jQuery("#numNewAnswers").val();
					if(isNaN(currAnswer)) {
						currAnswer = 0;
					}

					var s = "<tr>\r\n"+
						"<td align=\"right\" width=\"75\"><label for=\"newAnswer" + currAnswer + "\"><?php echo __("Answer", 'rsvp-plugin'); ?>:</label></td>\r\n" +
						"<td><input type=\"text\" name=\"newAnswer" + currAnswer + "\" id=\"newAnswer" + currAnswer + "\" size=\"40\" /></td>\r\n" +
					"</tr>\r\n";
					jQuery("#answerContainer").append(s);
					currAnswer++;
					jQuery("#numNewAnswers").val(currAnswer);
					return false;
				}

				jQuery(document).ready(function() {

					<?php
                    if ($isNew || !in_array($questionTypeId, $answerQuestionTypes)) {
                        echo 'jQuery("#answerContainer").hide();';
                    }

        if ($isNew || ($permissionLevel == "public")) {
            ?>
						jQuery("#attendeesArea").hide();
					<?php
        } ?>
					jQuery("#questionType").change(function() {
						var selectedValue = jQuery("#questionType").val();
						if(questionTypeId.indexOf(selectedValue) != -1) {
							jQuery("#answerContainer").show();
						} else {
							jQuery("#answerContainer").hide();
						}
					})

					jQuery("#permissionLevel").change(function() {
						if(jQuery("#permissionLevel").val() != "public") {
							jQuery("#attendeesArea").show();
						} else {
							jQuery("#attendeesArea").hide();
						}
					})
				});
			</script>
			<form name="contact" action="admin.php?page=rsvp-admin-custom-question" method="post">
				<input type="hidden" name="numNewAnswers" id="numNewAnswers" value="0" />
				<input type="hidden" name="questionId" value="<?php echo $questionId; ?>" />
				<?php wp_nonce_field('rsvp_add_custom_question'); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save', 'rsvp-plugin'); ?>" />
					<a href="<?php echo admin_url('admin.php?page=rsvp-admin-questions'); ?>"><?php _e('Back to custom question list', 'rsvp-plugin'); ?></a>
				</p>
				<table id="customQuestions" class="form-table">
					<tr valign="top">
						<th scope="row"><label for="questionType"><?php echo __("Question Type", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><select name="questionTypeID" id="questionType" size="1">
							<?php
                                foreach ($questionTypes as $qt) {
                                    echo "<option value=\"".$qt->id."\" ".(($questionTypeId == $qt->id) ? " selected=\"selected\"" : "").">".$qt->friendlyName."</option>\r\n";
                                } ?>
						</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="question"><?php echo __("Question", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="question" id="question" size="40" value="<?php echo htmlspecialchars($question); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="permissionLevel"><?php echo __("Question Permission Level", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><select name="permissionLevel" id="permissionLevel" size="1">
							<option value="public" <?php echo ($permissionLevel == "public") ? " selected=\"selected\"" : ""; ?>><?php echo __("Everyone", 'rsvp-plugin'); ?></option>
							<option value="private" <?php echo ($permissionLevel == "private") ? " selected=\"selected\"" : ""; ?>><?php echo __("Select People", 'rsvp-plugin'); ?></option>
						</select></td>
					</tr>
        <?php if (!$isNew && ($permissionLevel == "private")): ?>
						<tr>
							<th scope="row"><?php echo __("Private Import Key", 'rsvp-plugin'); ?>:</th>
							<td align="left">pq_<?php echo $questionId; ?></td>
						</tr>
        <?php endif; ?>
					<tr>
						<td colspan="2">
							<table cellpadding="0" cellspacing="0" border="0" id="answerContainer">
								<tr>
									<th><?php echo __("Answers", 'rsvp-plugin'); ?></th>
									<th align="right"><a href="#" onclick="return addAnswer();"><?php echo __("Add new Answer", 'rsvp-plugin'); ?></a></th>
								</tr>
								<?php
                                if (!$isNew) {
                                    $aRs = $wpdb->get_results($wpdb->prepare("SELECT id, answer FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $questionId));
                                    if (count($aRs) > 0) {
                                        foreach ($aRs as $answer) {
                                            ?>
											<tr>
												<td width="75" align="right"><label for="answer<?php echo $answer->id; ?>"><?php echo __("Answer", 'rsvp-plugin'); ?>:</label></td>
												<td><input type="text" name="answer<?php echo $answer->id; ?>" id="answer<?php echo $answer->id; ?>" size="40" value="<?php echo htmlspecialchars(stripslashes($answer->answer)); ?>" />
												 &nbsp; <input type="checkbox" name="deleteAnswer<?php echo $answer->id; ?>" id="deleteAnswer<?php echo $answer->id; ?>" value="Y" /><label for="deleteAnswer<?php echo $answer->id; ?>"><?php echo __("Delete", 'rsvp-plugin'); ?></label></td>
											</tr>
									<?php
                                        }
                                    }
                                } ?>
							</table>
						</td>
					</tr>
					<tr id="attendeesArea">
						<th scope="row"><label for="attendees"><?php echo __("Attendees allowed to answer this question", 'rsvp-plugin'); ?>:</label></th>
						<td>
							<p>
								<span style="margin-left: 30px;"><?php _e("Available people", "rsvp-plugin"); ?></span>
								<span style="margin-left: 65px;"><?php _e('People that have access', 'rsvp-plugin'); ?></span>
							</p>
							<select name="attendees[]" id="attendeesQuestionSelect" style="height:75px;" multiple="multiple">
							<?php
                                $attendees = $wpdb->get_results("SELECT id, firstName, lastName FROM ".$wpdb->prefix."attendees ORDER BY lastName, firstName");
        foreach ($attendees as $a) {
            ?>
								<option value="<?php echo $a->id; ?>"
												<?php echo((in_array($a->id, $savedAttendees)) ? " selected=\"selected\"" : ""); ?>><?php echo htmlspecialchars(stripslashes($a->firstName)." ".stripslashes($a->lastName)); ?></option>
							<?php
        } ?>
							</select>
						</td>
					</tr>
				</table>
			</form>
	<?php
    }
}

function rsvp_modify_menu()
{
    $page = add_menu_page(
        "RSVP Plugin",
                                "RSVP Plugin",
                                "publish_posts",
                                "rsvp-top-level",
                                "rsvp_admin_guestlist",
                                plugins_url("images/rsvp_lite_icon.png", RSVP_PLUGIN_FILE)
    );
    add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');

    $page = add_submenu_page(
        "rsvp-top-level",
                                     "Add Guest",
                                     "Add Guest",
                                     "publish_posts",
                                     "rsvp-admin-guest",
                                     "rsvp_admin_guest"
    );
    add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');

    add_submenu_page(
        "rsvp-top-level",
                                     "RSVP Export",
                                     "RSVP Export",
                                     "publish_posts",
                                     "rsvp-admin-export",
                                     "rsvp_admin_export"
    );
    add_submenu_page(
        "rsvp-top-level",
                                     "RSVP Import",
                                     "RSVP Import",
                                     "publish_posts",
                                     "rsvp-admin-import",
                                     "rsvp_admin_import"
    );
    $page = add_submenu_page(
        "rsvp-top-level",
                                     "Custom Questions",
                                     "Custom Questions",
                                     "publish_posts",
                                     "rsvp-admin-questions",
                                     "rsvp_admin_questions"
    );
    add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');

    $page = add_submenu_page(
        "rsvp-top-level",
                                     "Add Custom Question",
                                     "Add Custom Question",
                                     "publish_posts",
                                     "rsvp-admin-custom-question",
                                     "rsvp_admin_custom_question"
    );
    add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');

    $page = add_submenu_page(
        "rsvp-top-level",
                 'RSVP Options',    //page title
                   'RSVP Options',    //subpage title
                   'manage_options',    //access
                   'rsvp-options',        //current file
                   'rsvp_admin_guestlist_options'    //options function above
                   );
    add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');
}





/**
 * Handles converting text encodings for characters like umlauts that might be stored in different encodings
 *
 * @param  string $text The text we wish to handle the encoding against
 * @return string       The converted text
 */
function rsvp_handle_text_encoding($text)
{
    if (function_exists('mb_convert_encoding') && function_exists('mb_detect_encoding')) {
        return mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, ISO-8859-1', true));
    }

    return $text;
}

function rsvp_free_is_addslashes_enabled()
{
    return get_magic_quotes_gpc();
}

function rsvp_getCurrentPageURL()
{
	$pageURL = "//";
	$url = get_site_url();
	$server_info = parse_url($url);
	$domain = $server_info['host'];
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $domain.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $domain.$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

?>