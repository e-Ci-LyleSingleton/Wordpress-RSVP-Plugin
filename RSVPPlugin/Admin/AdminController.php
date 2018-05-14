<?php
namespace RSVPPlugin;

require_once ("Config.php");

abstract class CustomQuestionActions
{

    const ViewQuestion = 0;

    const AddNewQuestion = 1;

    const EditQuestion = 2;

    const RemoveQuestion = 3;
}

abstract class CustomQuestionPermissions
{

    const PrivatePermissions = 0;

    const PublicPermissions = 1;
}
;

abstract class GuestListActions
{

    const ViewGuests = 0;

    const BulkDelete = 1;

    const Delete = 2;
}

class AdminController
{

    private $config;

    function __construct($pluginConfiguration)
    {
        $this->config = $pluginConfiguration;
        
        add_action('admin_menu', [
            $this,
            'ConfigureMenu'
        ]);
    }

    private function OnActivate()
    {
        $this->config->EnsureDefaultCustomQuestionTypes();
    }

    public function InjectRequiredScripts()
    {
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
    }

    public function ConfigureMenu()
    {
        $page = add_menu_page("RSVP Plugin", "RSVP Plugin", "publish_posts", "rsvp-top-level", [
            $this,
            "RenderRsvpPluginHome"
        ], plugins_url("images/rsvp_lite_icon.png", __FILE__));
        add_action('admin_print_scripts-' . $page, [
            $this,
            'InjectRequiredScripts'
        ]);
        
        $page = add_submenu_page("rsvp-top-level", "Add Guest", "Add Guest", "publish_posts", "rsvp-admin-guest", [
            $this,
            "RenderEditGuest"
        ]);
        add_action('admin_print_scripts-' . $page, [
            $this,
            'InjectRequiredScripts'
        ]);
        
        // $page = add_submenu_page( "rsvp-top-level", "Add Custom Question", "Add Custom Question", "publish_posts", "rsvp-admin-questions", [$this,"RenderAddCustomQuestion"] );
        // add_action('admin_print_scripts-' . $page, [$this,'InjectRequiredScripts']);
        
        /*
         * add_submenu_page(
         * "rsvp-top-level",
         * "RSVP Export",
         * "RSVP Export",
         * "publish_posts",
         * "rsvp-admin-export",
         * "rsvp_admin_export"
         * );
         * add_submenu_page(
         * "rsvp-top-level",
         * "RSVP Import",
         * "RSVP Import",
         * "publish_posts",
         * "rsvp-admin-import",
         * "rsvp_admin_import"
         * );
         * $page = add_submenu_page(
         * "rsvp-top-level",
         * "Custom Questions",
         * "Custom Questions",
         * "publish_posts",
         * "rsvp-admin-questions",
         * "rsvp_admin_questions"
         * );
         * add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');
         *
         *
         * $page = add_submenu_page(
         * "rsvp-top-level",
         * 'RSVP Options', //page title
         * 'RSVP Options', //subpage title
         * 'manage_options', //access
         * 'rsvp-options', //current file
         * 'rsvp_admin_guestlist_options' //options function above
         * );
         * add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');
         */
    }

    /*
     * public function GetCustomQuestionRequestContext()
     * {
     *
     * }
     */
    
    /*
     * public function RenderAddCustomQuestion()
     * {
     * $requestContext = GetRequestContext();
     *
     * check_admin_referer( 'rsvp_add_custom_question' );
     *
     * switch ( $requestContext["action"] ) {
     * case CustomQuestionActions::EditQuestion:
     * UpdateCustomQuestion( $requestContext["questionText"], $requestContext["questionTypeLkp"], $requestContext["permissionLevel"] );
     * break;
     * case CustomQuestionActions::RemoveQuestion:
     * RemoveCustomQuestion( $questionId );
     *
     * foreach ($requestContext["selectedQuestionIds"] as $questionId) {
     * RemoveCustomResponse( $questionId );
     *
     * }
     * break;
     * case CustomQuestionActions::AddNewQuestion:
     * # code...
     * break;
     * case CustomQuestionActions::ViewQuestion:
     * default:
     * # code...
     * break;
     * }
     * }
     */
    public function RemoveAttendeeAssociations($attendeeId)
    {
        $this->config->GetDatabase()->query($this->config->GetDatabase()
            ->prepare("DELETE FROM " . $this->config->AssociatedAttendeesTable() . " WHERE attendeeID = %d OR associatedAttendeeID = %d", $attendeeId, $attendeeId));
    }

    public function RemoveAttendee($attendeeId)
    {
        $this->config->GetDatabase()->query($this->config->GetDatabase()
            ->prepare("DELETE FROM " . $this->config->AttendeesTable() . " WHERE id = %d", $attendeeId));
    }

    public function GetGuestListContext()
    {
        $context["action"] = null;
        $context["bulkAttendeeIds"] = [];
        $context["sortDir"] = "ASC";
        $context["sortKey"] = "lastName";
        
        $context["yesCount"] = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS yesCount FROM " . $this->config->AttendeesTable() . " WHERE rsvpStatus = 'Yes'")[0]->yesCount;
        $context["noCount"] = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS noCount FROM " . $this->config->AttendeesTable() . " WHERE rsvpStatus = 'No'")[0]->noCount;
        $context["noResponseCount"] = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS noResponseCount FROM " . $this->config->AttendeesTable() . " WHERE rsvpStatus = 'NoResponse'")[0]->noResponseCount;
        $context["kidsMealsCount"] = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS kidsMealCount FROM " . $this->config->AttendeesTable() . " WHERE kidsMeal = 'Y'")[0]->kidsMealCount;
        $context["veggiesMealsCount"] = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS veggieMealCount FROM " . $this->config->AttendeesTable() . " WHERE veggieMeal = 'Y'")[0]->veggieMealCount;
        
        if (isset($_POST["rsvp-bulk-action"]) && $_POST["rsvp-bulk-action"] == "delete") {
            if (isset($_POST["attendee"]) && is_array($_POST["attendee"])) {
                foreach ($_POST["attendee"] as $bulkActionId) {
                    if (is_numeric($bulkActionId) && $bulkActionId > 0) {
                        $context["bulkAttendeeIds"][] = $bulkActionId;
                    }
                }
                $context["action"] = GuestListActions::BulkDelete;
            }
        }
        if (isset($_GET["sortDirection"])) {
            $normalisedSortDir = strtolower($_GET['sortDirection']);
            if ($normalisedSortDir == "desc") {
                $context["sortDir"] = "DESC";
            } else if ($normalisedSortDir == "ASC") {
                $context["sortDir"] = "ASC";
            }
        }
        
        if (isset($_GET["sort"])) {
            $normalisedSortKey = strToLower($_GET['sort']);
            switch ($normalisedSortKey) {
                case 'rsvpstatus':
                    $context["sortKey"] = "rsvpStatus";
                    break;
                case 'kidsmeal':
                    $context["sortKey"] = "kidsMeal";
                    break;
                case 'additional':
                    $context["sortKey"] = "additionalAttendee";
                    break;
                case 'vegetarian':
                    $context["sortKey"] = "veggieMeal";
                    break;
                case 'attendee':
                default:
                    $context["sortKey"] = "lastName";
                    break;
            }
        }
        return $context;
    }

    public function GetAttendees($selectColumns, $sortColumn, $sortDir)
    {
        $sql = "SELECT ";
        foreach ($selectColumns as $columnName) {
            $sql .= $columnName . ",";
        }
        
        // need to remove last index of ","
        $sql = substr($sql, 0, strlen($sql) - 1);
        
        $sql .= " FROM " . $this->config->AttendeesTable() . " ORDER BY " . $sortColumn . " " . $sortDir;
        
        return $this->config->GetDatabase()->get_results($sql);
    }

    public function RenderRsvpPluginHome()
    {
        $context = $this->GetGuestListContext();
        
        switch ($context["action"]) {
            case GuestListActions::BulkDelete:
                foreach ($context["bulkAttendeeIds"] as $attendeeId) {
                    $this->RemoveAttendeeAssociations($attendeeId);
                    $this->RemoveAttendee($attendeeId);
                }
                break;
            case GuestListActions::ViewGuests:
            default:
                // code...
                break;
        }
        $attendees = $this->GetAttendees([
            "id",
            "firstName",
            "lastName",
            "rsvpStatus",
            "note",
            "kidsMeal",
            "additionalAttendee",
            "veggieMeal",
            "personalGreeting",
            "passcode",
            "email",
            "rsvpDate"
        ], $context["sortKey"], $context["sortDir"]);
        ?>

<div class="updated">
	<p><?php
        
echo __("Need some of the <a href=\"https://www.rsvpproplugin.com\" target=\"_blank\">features of the premium version</a>?
            Want to save <b>20%</b> on the cost of the premium version?
            <a href=\"https://www.rsvpproplugin.com/rsvp-premium-discount-code/\">Click here</a>.", 'rsvp-plugin');
        ?>
            </p>
</div>
<script type="text/javascript" language="javascript">
                jQuery(document).ready(function() {
                    jQuery("#cb").click(function() {
                        if(jQuery("#cb").attr("checked")) {
                            jQuery("input[name='attendee[]']").attr("checked", "checked");
                        } else {
                            jQuery("input[name='attendee[]']").removeAttr("checked");
                        }
                    });
                });
            </script>
<div class="wrap">
	<div id="icon-edit" class="icon32">
		<br />
	</div>
	<h2><?php echo __("List of current attendees", 'rsvp-plugin'); ?></h2>
	<form method="post" id="rsvp-form" enctype="multipart/form-data">
		<input type="hidden" id="rsvp-bulk-action" name="rsvp-bulk-action" />
		<input type="hidden" id="sortValue" name="sortValue"
			value="<?php echo htmlentities($context["sortKey"], ENT_QUOTES); ?>" />
		<input type="hidden" name="exportSortDirection"
			value="<?php echo htmlentities($context["sortDir"], ENT_QUOTES); ?>" />
		<div class="tablenav">
			<div class="alignleft actions">
				<select id="rsvp-action-top" name="action">
					<option value="" selected="selected"><?php _e('Bulk Actions', 'rsvp-plugin'); ?></option>
					<option value="delete"><?php _e('Delete', 'rsvp-plugin'); ?></option>
				</select> <input type="submit"
					value="<?php _e('Apply', 'rsvp-plugin'); ?>" name="doaction"
					id="doaction" class="button-secondary action"
					onclick="document.getElementById('rsvp-bulk-action').value = document.getElementById('rsvp-action-top').value;" />
				<input type="submit"
					value="<?php _e('Export Attendees', 'rsvp-plugin'); ?>"
					name="exportButton" id="exportButton"
					class="button-secondary action"
					onclick="document.getElementById('rsvp-bulk-action').value = 'export';" />
			</div>
                        <?php ?>
                        <div class="alignright"><?php __("RSVP Count -", 'rsvp-plugin'); ?>
                            <?php echo __("Yes:", 'rsvp-plugin'); ?> <strong><?php echo $context["yesCount"]; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("No:", 'rsvp-plugin'); ?> <strong><?php echo $context["noCount"]; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("No Response:", 'rsvp-plugin'); ?> <strong><?php echo $context["noResponseCount"]; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("Kids Meals:", 'rsvp-plugin'); ?> <strong><?php echo $context["kidsMealsCount"]; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("Veggie Meals:", 'rsvp-plugin'); ?> <strong><?php echo $context["veggiesMealsCount"]; ?></strong>
			</div>
			<div class="clear"></div>
		</div>
		<table class="widefat post fixed" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"
						style=""><input type="checkbox" id="cb" /></th>
					<th scope="col" id="attendeeName"
						class="manage-column column-title" style=""><?php echo __("Attendee", 'rsvp-plugin'); ?><br />
						<a
						href="admin.php?page=rsvp-top-level&amp;sort=attendee&amp;sortDirection=asc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
        echo ((($context["sortKey"] == "attendee") && ($context["sortDir"] == "asc")) ? "_selected" : "");
        ?>.gif"
							width="11" height="9" alt="Sort Ascending Attendee Status"
							title="Sort Ascending Attendee Status" border="0">
					</a> &nbsp; <a
						href="admin.php?page=rsvp-top-level&amp;sort=attendee&amp;sortDirection=desc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
        echo ((($context["sortKey"] == "attendee") && ($context["sortDir"] == "desc")) ? "_selected" : "");
        ?>.gif"
							width="11" height="9" alt="Sort Descending Attendee Status"
							title="Sort Descending Attendee Status" border="0">
					</a></th>
					<!--<th scope="col" id="rsvpEmail" class="manage-column column-title"><?php echo __("Email", 'rsvp-plugin'); ?></th>-->
					<th scope="col" id="rsvpStatus" class="manage-column column-title"
						style=""><?php echo __("RSVP Status", 'rsvp-plugin'); ?><br /> <a
						href="admin.php?page=rsvp-top-level&amp;sort=rsvpStatus&amp;sortDirection=asc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php echo((($context["sortKey"] == "rsvpStatus") && ($context["sortDir"] == "asc")) ? "_selected" : ""); ?>.gif"
							width="11" height="9" alt="Sort Ascending RSVP Status"
							title="Sort Ascending RSVP Status" border="0">
					</a> &nbsp; <a
						href="admin.php?page=rsvp-top-level&amp;sort=rsvpStatus&amp;sortDirection=desc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
        echo ((($context["sortKey"] == "rsvpStatus") && ($context["sortDir"] == "desc")) ? "_selected" : "");
        ?>.gif"
							width="11" height="9" alt="Sort Descending RSVP Status"
							title="Sort Descending RSVP Status" border="0">
					</a></th>
					<th scope="col" id="rsvpDate" class="manage-column column-title"><?php echo __("RSVP Date", 'rsvp-pro-plugin'); ?></th>
                            <?php
        
if (get_option(Config::OPTION_HIDE_KIDS_MEAL) != "Y") {
            ?>
                            <th scope="col" id="kidsMeal"
						class="manage-column column-title" style=""><?php echo __("Kids Meal", 'rsvp-plugin'); ?><br />
						<a
						href="admin.php?page=rsvp-top-level&amp;sort=kidsMeal&amp;sortDirection=asc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
            echo ((($context["sortKey"] == "kidsMeal") && ($context["sortDir"] == "asc")) ? "_selected" : "");
            ?>.gif"
							width="11" height="9" alt="Sort Ascending Kids Meal Status"
							title="Sort Ascending Kids Meal Status" border="0">
					</a> &nbsp; <a
						href="admin.php?page=rsvp-top-level&amp;sort=kidsMeal&amp;sortDirection=desc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
            echo ((($context["sortKey"] == "kidsMeal") && ($context["sortDir"] == "desc")) ? "_selected" : "");
            ?>.gif"
							width="11" height="9" alt="Sort Descending Kids Meal Status"
							title="Sort Descending Kids Meal Status" border="0">
					</a></th>
                            <?php
        }
        ?>
                            <th scope="col" id="additionalAttendee"
						class="manage-column column-title" style=""><?php echo __("Additional Attendee", 'rsvp-plugin'); ?> <br />
						<a
						href="admin.php?page=rsvp-top-level&amp;sort=additional&amp;sortDirection=asc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
        echo ((($context["sortKey"] == "additional") && ($context["sortDir"] == "asc")) ? "_selected" : "");
        ?>.gif"
							width="11" height="9"
							alt="Sort Ascending Additional Attendees Status"
							title="Sort Ascending Additional Attendees Status" border="0">
					</a> &nbsp; <a
						href="admin.php?page=rsvp-top-level&amp;sort=additional&amp;sortDirection=desc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
        echo ((($context["sortKey"] == "additional") && ($context["sortDir"] == "desc")) ? "_selected" : "");
        ?>.gif"
							width="11" height="9"
							alt="Sort Descending Additional Attendees Status"
							title="Sort Descending Additional Atttendees Status" border="0">
					</a></th>
                            <?php
        
if (get_option(Config::OPTION_HIDE_VEGGIE) != "Y") {
            ?>
                            <th scope="col" id="veggieMeal"
						class="manage-column column-title" style=""><?php echo __("Vegetarian", 'rsvp-plugin'); ?> <br />
						<a
						href="admin.php?page=rsvp-top-level&amp;sort=vegetarian&amp;sortDirection=asc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
            echo ((($context["sortKey"] == "vegetarian") && ($context["sortDir"] == "asc")) ? "_selected" : "");
            ?>.gif"
							width="11" height="9" alt="Sort Ascending Vegetarian Status"
							title="Sort Ascending Vegetarian Status" border="0">
					</a> &nbsp; <a
						href="admin.php?page=rsvp-top-level&amp;sort=vegetarian&amp;sortDirection=desc">
							<img
							src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
            echo ((($context["sortKey"] == "vegetarian") && ($context["sortDir"] == "desc")) ? "_selected" : "");
            ?>.gif"
							width="11" height="9" alt="Sort Descending Vegetarian Status"
							title="Sort Descending Vegetarian Status" border="0">
					</a></th>
                            <?php
        }
        ?>
                            <!--<th scope="col" id="customMessage" class="manage-column column-title" style=""><?php echo __("Custom Message", 'rsvp-plugin'); ?></th>-->
					<th scope="col" id="note" class="manage-column column-title"
						style=""><?php echo __("Note", 'rsvp-plugin'); ?></th>
					<th scope="col" id="passcode" class="manage-column column-title"
						style=""><?php echo __("Passcode", 'rsvp-plugin'); ?></th>
                            <?php
        $qRs = $this->config->GetDatabase()->get_results("SELECT id, question FROM " . $this->config->QuestionsTable() . " ORDER BY 
                                sortOrder, id");
        if (count($qRs) > 0) {
            foreach ($qRs as $q) {
                ?>
                                <th scope="col"
						class="manage-column -column-title"><?php echo htmlspecialchars(stripslashes($q->question)); ?></th>
                            <?php
            }
        }
        ?>
                            <th scope="col" id="associatedAttendees"
						class="manage-column column-title" style=""><?php echo __("Associated Attendees", 'rsvp-plugin'); ?></th>
				</tr>
			</thead>
		</table>
		<div>
			<table class="widefat post fixed" cellspacing="0">
                    <?php
        $i = 0;
        foreach ($attendees as $attendee) {
            ?>
                            <tr
					class="<?php echo(($i % 2 == 0) ? "alternate" : ""); ?> author-self">
					<th scope="row" class="check-column"><input type="checkbox"
						name="attendee[]" value="<?php echo $attendee->id; ?>" /></th>
					<td><a
						href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-guest&amp;id=<?php echo $attendee->id; ?>"><?php echo htmlspecialchars(stripslashes($attendee->firstName)." ".stripslashes($attendee->lastName)); ?></a>
					</td>
					<!--<td><?php echo htmlspecialchars(stripslashes($attendee->email)); ?></td>-->
					<td><?php echo $attendee->rsvpStatus; ?></td>
					<td><?php echo $attendee->rsvpDate; ?></td>
                                <?php
            
if (get_option(Config::OPTION_HIDE_KIDS_MEAL) != "Y") {
                ?>
                                <td><?php
                if ($attendee->rsvpStatus == "NoResponse") {
                    echo "--";
                } else {
                    echo (($attendee->kidsMeal == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
                }
                ?></td>
                                    <?php
            }
            ?>
                                <td><?php
            if ($attendee->rsvpStatus == "NoResponse") {
                echo "--";
            } else {
                echo (($attendee->additionalAttendee == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
            }
            ?></td>

                                <?php
            
if (get_option(Config::OPTION_HIDE_VEGGIE) != "Y") {
                ?>
                                <td><?php
                if ($attendee->rsvpStatus == "NoResponse") {
                    echo "--";
                } else {
                    echo (($attendee->veggieMeal == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
                }
                ?></td>
                                <?php
            }
            ?>
                                <!--<td><?php
            echo nl2br(stripslashes(trim($attendee->personalGreeting)));
            ?></td>-->
					<td><?php echo nl2br(esc_html(stripslashes(trim($attendee->note)))); ?></td>
                                <?php
            if (get_option(Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE) == "Y" || get_option(Config::OPTION_CONFIG_ALLOW_PASSCODE) == "Y") {
                ?>
                                    <td><?php echo $attendee->passcode; ?></td>
                                <?php
            }
            $sql = "SELECT question, answer FROM " . $this->config->QuestionsTable() . " q
                                        LEFT JOIN " . $this->config->AttendeeAnswersTable() . " ans ON q.id = ans.questionID AND ans.attendeeID = %d
                                        ORDER BY q.sortOrder, q.id";
            $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()
                ->prepare($sql, $attendee->id));
            if (count($aRs) > 0) {
                foreach ($aRs as $a) {
                    ?>
                                        <td><?php echo htmlspecialchars(stripslashes($a->answer)); ?></td>
                                <?php
                }
            }
            ?>
                                <td>
                                <?php
            $sql = "SELECT firstName, lastName FROM " . $this->config->AttendeesTable() . "
                                        WHERE id IN (SELECT attendeeID FROM " . $this->config->AssociatedAttendeesTable() . " WHERE associatedAttendeeID = %d)
                                            OR id in (SELECT associatedAttendeeID FROM " . $this->config->AssociatedAttendeesTable() . " WHERE attendeeID = %d)";
            
            $associations = $this->config->GetDatabase()->get_results($this->config->GetDatabase()
                ->prepare($sql, $attendee->id, $attendee->id));
            foreach ($associations as $a) {
                echo htmlspecialchars(stripslashes($a->firstName . " " . $a->lastName)) . "<br />";
            }
            ?>
                                </td>
				</tr>
                        <?php
            $i ++;
        }
        ?>
                    </table>
		</div>
	</form>
</div>
<?php
    }

    public function RenderEditGuest()
    {
        if ((count($_POST) > 0) && ! empty($_POST['firstName']) && ! empty($_POST['lastName'])) {
            check_admin_referer('rsvp_add_guest');
            $passcode = (isset($_POST['passcode'])) ? $_POST['passcode'] : "";
            
            if (isset($_POST['attendeeId']) && is_numeric($_POST['attendeeId']) && ($_POST['attendeeId'] > 0)) {
                $this->config->GetDatabase()->update($this->config->AttendeesTable(), array(
                    "firstName" => trim($_POST['firstName']),
                    "lastName" => trim($_POST['lastName']),
                    "email" => trim($_POST['email']),
                    "personalGreeting" => trim($_POST['personalGreeting']),
                    "rsvpStatus" => trim($_POST['rsvpStatus'])
                ), array(
                    "id" => $_POST['attendeeId']
                ), array(
                    "%s",
                    "%s",
                    "%s",
                    "%s",
                    "%s"
                ), array(
                    "%d"
                ));
                $attendeeId = $_POST['attendeeId'];
                $this->config->GetDatabase()->query($this->config->GetDatabase()
                    ->prepare("DELETE FROM " . $this->config->AssociatedAttendeesTable() . " WHERE attendeeId = %d", $attendeeId));
                $this->config->GetDatabase()->query($this->config->GetDatabase()
                    ->prepare("DELETE FROM " . $this->config->AssociatedAttendeesTable() . " WHERE associatedAttendeeID = %d", $attendeeId));
            } else {
                $this->config->GetDatabase()->insert($this->config->AttendeesTable(), array(
                    "firstName" => trim($_POST['firstName']),
                    "lastName" => trim($_POST['lastName']),
                    "email" => trim($_POST['email']),
                    "personalGreeting" => trim($_POST['personalGreeting']),
                    "rsvpStatus" => trim($_POST['rsvpStatus'])
                ), array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ));
                
                $attendeeId = $this->config->GetDatabase()->insert_id;
            }
            if (isset($_POST['associatedAttendees']) && is_array($_POST['associatedAttendees'])) {
                foreach ($_POST['associatedAttendees'] as $aid) {
                    if (is_numeric($aid) && ($aid > 0)) {
                        $this->config->GetDatabase()->insert($this->config->AssociatedAttendeesTable(), array(
                            "attendeeID" => $attendeeId,
                            "associatedAttendeeID" => $aid
                        ), array(
                            "%d",
                            "%d"
                        ));
                        $this->config->GetDatabase()->insert($this->config->AssociatedAttendeesTable(), array(
                            "attendeeID" => $aid,
                            "associatedAttendeeID" => $attendeeId
                        ), array(
                            "%d",
                            "%d"
                        ));
                    }
                }
            }
            
            if (get_option(Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE) == "Y" || get_option(Config::OPTION_CONFIG_ALLOW_PASSCODE) == "Y") {
                if (empty($passcode)) {
                    $passcode = rsvp_generate_passcode();
                }
                if (rsvp_require_unique_passcode() && ! rsvp_is_passcode_unique($passcode, $attendeeId)) {
                    $passcode = rsvp_generate_passcode();
                }
                $this->config->GetDatabase()->update($this->config->AttendeesTable(), array(
                    "passcode" => trim($passcode)
                ), array(
                    "id" => $attendeeId
                ), array(
                    "%s"
                ), array(
                    "%d"
                ));
            }
            ?>
<p><?php echo __("Attendee", 'rsvp-plugin'); ?> <?php echo htmlspecialchars(stripslashes($_POST['firstName']." ".$_POST['lastName'])); ?> <?php echo __("has been successfully saved", 'rsvp-plugin'); ?></p>
<p>
	<a
		href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-top-level"><?php echo __("Continue to Attendee List", 'rsvp-plugin'); ?></a>
	| <a
		href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-guest"><?php echo __("Add a Guest", 'rsvp-plugin'); ?></a>
</p>
<?php
        } else {
            $attendee = null;
            $associatedAttendees = array();
            $firstName = "";
            $lastName = "";
            $email = "";
            $personalGreeting = "";
            $rsvpStatus = "NoResponse";
            $passcode = "";
            $attendeeId = 0;
            
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $attendee = $this->config->GetDatabase()->get_row("SELECT id, firstName, lastName, email, personalGreeting, rsvpStatus, passcode FROM " . $this->config->AttendeesTable() . " WHERE id = " . $_GET['id']);
                if ($attendee != null) {
                    $attendeeId = $attendee->id;
                    $firstName = stripslashes($attendee->firstName);
                    $lastName = stripslashes($attendee->lastName);
                    $email = stripslashes($attendee->email);
                    $personalGreeting = stripslashes($attendee->personalGreeting);
                    $rsvpStatus = $attendee->rsvpStatus;
                    $passcode = stripslashes($attendee->passcode);
                    
                    // Get the associated attendees and add them to an array
                    $associations = $this->config->GetDatabase()->get_results("SELECT associatedAttendeeID FROM " . $this->config->AssociatedAttendeesTable() . " WHERE attendeeId = " . $attendee->id . " UNION " . "SELECT attendeeID FROM " . $this->config->AssociatedAttendeesTable() . " WHERE associatedAttendeeID = " . $attendee->id);
                    foreach ($associations as $aId) {
                        $associatedAttendees[] = $aId->associatedAttendeeID;
                    }
                }
            }
            ?>
<form name="contact" action="admin.php?page=rsvp-admin-guest"
	method="post">
                    <?php wp_nonce_field('rsvp_add_guest'); ?>
            <input type="hidden" name="attendeeId"
		value="<?php echo $attendeeId; ?>" />
	<p class="submit">
		<input type="submit" class="button-primary"
			value="<?php _e('Save', 'rsvp-plugin'); ?>" />
	</p>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="firstName"><?php echo __("First Name", 'rsvp-plugin'); ?>:</label></th>
			<td align="left"><input type="text" name="firstName" id="firstName"
				size="30" value="<?php echo htmlspecialchars($firstName); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="lastName"><?php echo __("Last Name", 'rsvp-plugin'); ?>:</label></th>
			<td align="left"><input type="text" name="lastName" id="lastName"
				size="30" value="<?php echo htmlspecialchars($lastName); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="email"><?php echo __("Email", 'rsvp-plugin'); ?>:</label></th>
			<td align="left"><input type="text" name="email" id="email" size="30"
				value="<?php echo htmlspecialchars($email); ?>" /></td>
		</tr>
                        <?php
            if (get_option(Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE) == "Y" || get_option(Config::OPTION_CONFIG_ALLOW_PASSCODE) == "Y") {
                ?>
                            <tr valign="top">
			<th scope="row"><label for="passcode"><?php echo __("Passcode", 'rsvp-plugin'); ?>:</label></th>
			<td align="left"><input type="text" name="passcode" id="passcode"
				size="30" value="<?php echo htmlspecialchars($passcode); ?>" /></td>
		</tr>
                        <?php
            }
            ?>
                        <tr>
			<th scope="row"><label for="rsvpStatus"><?php echo __("RSVP Status", 'rsvp-plugin'); ?></label></th>
			<td align="left"><select name="rsvpStatus" id="rsvpStatus" size="1">
					<option value="NoResponse"
						<?php
            echo (($rsvpStatus == "NoResponse") ? " selected=\"selected\"" : "");
            ?>><?php echo __("No Response", 'rsvp-plugin'); ?></option>
					<option value="Yes"
						<?php
            echo (($rsvpStatus == "Yes") ? " selected=\"selected\"" : "");
            ?>><?php echo __("Yes", 'rsvp-plugin'); ?></option>
					<option value="No"
						<?php
            echo (($rsvpStatus == "No") ? " selected=\"selected\"" : "");
            ?>><?php echo __("No", 'rsvp-plugin'); ?></option>
			</select></td>
		</tr>
		<tr valign="top">
			<th scope="row" valign="top"><label for="personalGreeting"><?php echo __("Custom Message", 'rsvp-plugin'); ?>:</label></th>
			<td align="left"><textarea name="personalGreeting"
					id="personalGreeting" rows="5" cols="40"><?php echo htmlspecialchars($personalGreeting); ?></textarea></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php echo __("Associated Attendees", 'rsvp-plugin'); ?>:</th>
			<td align="left">
				<p>
					<span style="margin-left: -5px;"><?php _e("Non-Associated Attendees", "rsvp-plugin"); ?></span>
					<span style="margin-left: 26px;"><?php _e('Associated Attendees', 'rsvp-plugin'); ?></span>
				</p> <select name="associatedAttendees[]"
				id="associatedAttendeesSelect" multiple="multiple" size="5"
				style="height: 200px;">
                                    <?php
            $attendees = $this->config->GetDatabase()->get_results("SELECT id, firstName, lastName FROM " . $this->config->AttendeesTable() . " ORDER BY lastName, firstName");
            foreach ($attendees as $a) {
                if ($a->id != $attendeeId) {
                    ?>
                                                <option
						value="<?php echo $a->id; ?>"
						<?php echo((in_array($a->id, $associatedAttendees)) ? "selected=\"selected\"" : ""); ?>><?php echo htmlspecialchars(stripslashes($a->firstName)." ".stripslashes($a->lastName)); ?></option>
                                    <?php
                }
            }
            ?>
                                </select>
			</td>
		</tr>
                    <?php
            if (($attendee != null) && ($attendee->id > 0)) {
                $sql = "SELECT question, answer FROM " . $this->config->AttendeeAnswersTable() . " ans
                            INNER JOIN " . $this->config->QuestionsTable() . " q ON q.id = ans.questionID
                            WHERE attendeeID = %d
                            ORDER BY q.sortOrder";
                $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()
                    ->prepare($sql, $attendee->id));
                if (count($aRs) > 0) {
                    ?>
                    <tr>
			<td colspan="2">
				<h4><?php echo __("Custom Questions Answered", 'rsvp-plugin'); ?></h4>
				<table cellpadding="2" cellspacing="0" border="0">
					<tr>
						<th><?php echo __("Question", 'rsvp-plugin'); ?></th>
						<th><?php echo __("Answer", 'rsvp-plugin'); ?></th>
					</tr>
                    <?php
                    foreach ($aRs as $a) {
                        ?>
                                <tr>
						<td><?php echo stripslashes($a->question); ?></td>
						<td><?php echo str_replace("||", ", ", stripslashes($a->answer)); ?></td>
					</tr>
                    <?php
                    }
                    ?>
                            </table>
			</td>
		</tr>
                    <?php
                }
            }
            ?>
                    </table>
	<p class="submit">
		<input type="submit" class="button-primary"
			value="<?php _e('Save', 'rsvp-plugin'); ?>" />
	</p>
</form>
<?php
        }
    }
}

?>
