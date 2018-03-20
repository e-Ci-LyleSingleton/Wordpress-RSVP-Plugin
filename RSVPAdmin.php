<?php

namespace RSVPPlugin;

require_once("Config.php");

if ( !class_exists( 'RSVPAdmin' ) ) {

    abstract class CustomQuestionActions
    {
        const ViewQuestion = 0;
        const AddNewQuestion = 1;
        const EditQuestion = 2;
        const RemoveQuestion = 3;
    }

    class RSVPAdmin
    {
        private $config;

        function __construct( $pluginConfiguration )
        {
            $this->config = $pluginConfiguration;

            add_action('admin_menu', [$this,'ConfigureMenu']);
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

            $page = add_menu_page( "RSVP Plugin", "RSVP Plugin", "publish_posts", "rsvp-top-level", [$this,"RenderRsvpPluginHome"], plugins_url("images/rsvp_lite_icon.png", __FILE__) );
            add_action('admin_print_scripts-' . $page, [$this,'InjectRequiredScripts']);
    
            $page = add_submenu_page( "rsvp-top-level", "Add Guest", "Add Guest", "publish_posts", "rsvp-admin-guest", [$this,"RenderEditGuest"] );
            add_action('admin_print_scripts-' . $page, [$this,'InjectRequiredScripts']);
    
            $page = add_submenu_page( "rsvp-top-level", "Add Custom Question", "Add Custom Question", "publish_posts", "rsvp-admin-questions", [$this,"RenderAddCustomQuestion"] );
            add_action('admin_print_scripts-' . $page, [$this,'InjectRequiredScripts']);


        /*add_submenu_page(
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
                     'RSVP Options',    //page title
                       'RSVP Options',    //subpage title
                       'manage_options',    //access
                       'rsvp-options',        //current file
                       'rsvp_admin_guestlist_options'    //options function above
                       );
        add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');*/
        
        }
        
        public function GetCustomQuestionRequestContext()
        {

        }


        public function RenderAddCustomQuestion()
        {
            $requestContext = GetRequestContext();

            check_admin_referer( 'rsvp_add_custom_question' );

            if( $requestContext["action"] == CustomQuestionActions::EditQuestion )
            {
                UpdateCustomQuestion( $requestContext["question"], $requestContext["questionTypeLkp"], $requestContext["permissionLevel"] );
            } else 
            {

            }

            //$answerQuestionTypes = rsvp_get_question_with_answer_type_ids();
                
            if ((count($_POST) > 0) && !empty($_POST['question']) && is_numeric($_POST['questionTypeID'])) {

                if (isset($_POST['questionId']) && is_numeric($_POST['questionId']) && ($_POST['questionId'] > 0)) {
                    $this->config->GetDatabase()->update(
                        QUESTIONS_TABLE,
                            array("question" => trim($_POST['question']),
                                    "questionTypeID" => trim($_POST['questionTypeID']),
                                        "permissionLevel" => ((trim($_POST['permissionLevel']) == "private") ? "private" : "public")),
                            array("id" => $_POST['questionId']),
                            array("%s", "%d", "%s"),
                            array("%d")
                    );


                    $questionId = $_POST['questionId'];
        
                    $answers = $this->config->GetDatabase()->get_results(
                        $this->config->GetDatabase()->prepare("SELECT id FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $requestContext["questionId"]));
                    if (count($answers) > 0) 
                    {
                        foreach ($answers as $a) {
                            if (isset($_POST['deleteAnswer'.$a->id]) && (strToUpper($_POST['deleteAnswer'.$a->id]) == "Y")) {
                                $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare("DELETE FROM ".QUESTION_ANSWERS_TABLE." WHERE id = %d", $a->id));
                            } elseif (isset($_POST['answer'.$a->id]) && !empty($_POST['answer'.$a->id])) {
                                $this->config->GetDatabase()->update(
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
                    $this->config->GetDatabase()->insert(
                        QUESTIONS_TABLE,
                        array("question" => trim($_POST['question']),
                                                         "questionTypeID" => trim($_POST['questionTypeID']),
                                                                                             "permissionLevel" => ((trim($_POST['permissionLevel']) == "private") ? "private" : "public")),
                                                   array('%s', '%d', '%s')
                    );
                    $questionId = $this->config->GetDatabase()->insert_id;
                }
        
                if (isset($_POST['numNewAnswers']) && is_numeric($_POST['numNewAnswers']) &&
                   in_array($_POST['questionTypeID'], $answerQuestionTypes)) {
                    for ($i = 0; $i < $_POST['numNewAnswers']; $i++) {
                        if (isset($_POST['newAnswer'.$i]) && !empty($_POST['newAnswer'.$i])) {
                            $this->config->GetDatabase()->insert(QUESTION_ANSWERS_TABLE, array("questionID"=>$questionId, "answer"=>$_POST['newAnswer'.$i]));
                        }
                    }
                }
        
                if (strToLower(trim($_POST['permissionLevel'])) == "private") {
                    $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare("DELETE FROM ".QUESTION_ATTENDEES_TABLE." WHERE questionID = %d", $questionId));
                    if (isset($_POST['attendees']) && is_array($_POST['attendees'])) {
                        foreach ($_POST['attendees'] as $aid) {
                            if (is_numeric($aid) && ($aid > 0)) {
                                $this->config->GetDatabase()->insert(QUESTION_ATTENDEES_TABLE, array("attendeeID"=>$aid, "questionID"=>$questionId), array("%d", "%d"));
                            }
                        }
                    }
                }
                
                ?>
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
                    $qRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare("SELECT id, question, questionTypeID, permissionLevel FROM ".QUESTIONS_TABLE." WHERE id = %d", $_GET['id']));
                    if (count($qRs) > 0) {
                        $isNew = false;
                        $questionId = $qRs[0]->id;
                        $question = stripslashes($qRs[0]->question);
                        $permissionLevel = stripslashes($qRs[0]->permissionLevel);
                        $questionTypeId = $qRs[0]->questionTypeID;
        
                        if ($permissionLevel == "private") {
                            $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare("SELECT attendeeID FROM ".QUESTION_ATTENDEES_TABLE." WHERE questionID = %d", $questionId));
                            if (count($aRs) > 0) {
                                foreach ($aRs as $a) {
                                    $savedAttendees[] = $a->attendeeID;
                                }
                            }
                        }
                    }
                }
        
                $sql = "SELECT id, questionType, friendlyName FROM ".QUESTION_TYPE_TABLE;
                $questionTypes = $this->config->GetDatabase()->get_results($sql); ?>
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
                                            $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare("SELECT id, answer FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $questionId));
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
                                        $attendees = $this->config->GetDatabase()->get_results("SELECT id, firstName, lastName FROM ".$this->config->GetDatabase()->prefix."attendees ORDER BY lastName, firstName");
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

        public function RenderRsvpPluginHome()
        {

            if ((count($_POST) > 0) && ($_POST['rsvp-bulk-action'] == "delete") && (is_array($_POST['attendee']) && (count($_POST['attendee']) > 0))) {
                foreach ($_POST['attendee'] as $attendee) {
                    if (is_numeric($attendee) && ($attendee > 0)) {
                        $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare(
                            "DELETE FROM ".$this->config->AssociatedAttendeesTable()." WHERE attendeeID = %d OR associatedAttendeeID = %d",
                                                                                $attendee,
                                                                                $attendee
                        ));
                        $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare(
                            "DELETE FROM ".$this->config->AttendeesTable()." WHERE id = %d",
                                                                                $attendee
                        ));
                    }
                }
            }

            $sql = "SELECT id, firstName, lastName, rsvpStatus, note, kidsMeal, additionalAttendee, veggieMeal, personalGreeting, passcode, email, rsvpDate FROM ".$this->config->AttendeesTable();
            $orderBy = " lastName, firstName";
            if (isset($_GET['sort'])) {
                if (strToLower($_GET['sort']) == "rsvpstatus") {
                    $orderBy = " rsvpStatus ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
                } elseif (strToLower($_GET['sort']) == "attendee") {
                    $direction = ((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC");
                    $orderBy = " lastName $direction, firstName $direction";
                } elseif (strToLower($_GET['sort']) == "kidsmeal") {
                    $orderBy = " kidsMeal ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
                } elseif (strToLower($_GET['sort']) == "additional") {
                    $orderBy = " additionalAttendee ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
                } elseif (strToLower($_GET['sort']) == "vegetarian") {
                    $orderBy = " veggieMeal ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
                }
            }
            $sql .= " ORDER BY ".$orderBy;
            $attendees = $this->config->GetDatabase()->get_results($sql);
            $sort = "";
            $sortDirection = "asc";
            if (isset($_GET['sort'])) {
                $sort = $_GET['sort'];
            }

            if (isset($_GET['sortDirection'])) {
                $sortDirection = $_GET['sortDirection'];
            } ?>
        <div class="updated">
        <p><?php echo __("Need some of the <a href=\"https://www.rsvpproplugin.com\" target=\"_blank\">features of the premium version</a>?
            Want to save <b>20%</b> on the cost of the premium version?
            <a href=\"https://www.rsvpproplugin.com/rsvp-premium-discount-code/\">Click here</a>.", 'rsvp-plugin'); ?></p>
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
                <div id="icon-edit" class="icon32"><br /></div>
                <h2><?php echo __("List of current attendees", 'rsvp-plugin'); ?></h2>
                <form method="post" id="rsvp-form" enctype="multipart/form-data">
                    <input type="hidden" id="rsvp-bulk-action" name="rsvp-bulk-action" />
                    <input type="hidden" id="sortValue" name="sortValue" value="<?php echo htmlentities($sort, ENT_QUOTES); ?>" />
                    <input type="hidden" name="exportSortDirection" value="<?php echo htmlentities($sortDirection, ENT_QUOTES); ?>" />
                    <div class="tablenav">
                        <div class="alignleft actions">
                            <select id="rsvp-action-top" name="action">
                                <option value="" selected="selected"><?php _e('Bulk Actions', 'rsvp-plugin'); ?></option>
                                <option value="delete"><?php _e('Delete', 'rsvp-plugin'); ?></option>
                            </select>
                            <input type="submit" value="<?php _e('Apply', 'rsvp-plugin'); ?>" name="doaction" id="doaction" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = document.getElementById('rsvp-action-top').value;" />
                            <input type="submit" value="<?php _e('Export Attendees', 'rsvp-plugin'); ?>" name="exportButton" id="exportButton" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = 'export';" />
                        </div>
                        <?php
                            $yesResults = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS yesCount FROM ".$this->config->AttendeesTable()." WHERE rsvpStatus = 'Yes'");
            $noResults = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS noCount FROM ".$this->config->AttendeesTable()." WHERE rsvpStatus = 'No'");
            $noResponseResults = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS noResponseCount FROM ".$this->config->AttendeesTable()." WHERE rsvpStatus = 'NoResponse'");
            $kidsMeals = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS kidsMealCount FROM ".$this->config->AttendeesTable()." WHERE kidsMeal = 'Y'");
            $veggieMeals = $this->config->GetDatabase()->get_results("SELECT COUNT(*) AS veggieMealCount FROM ".$this->config->AttendeesTable()." WHERE veggieMeal = 'Y'"); ?>
                        <div class="alignright"><?php __("RSVP Count -", 'rsvp-plugin'); ?>
                            <?php echo __("Yes:", 'rsvp-plugin'); ?> <strong><?php echo $yesResults[0]->yesCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("No:", 'rsvp-plugin'); ?> <strong><?php echo $noResults[0]->noCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("No Response:", 'rsvp-plugin'); ?> <strong><?php echo $noResponseResults[0]->noResponseCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("Kids Meals:", 'rsvp-plugin'); ?> <strong><?php echo $kidsMeals[0]->kidsMealCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp;
                            <?php echo __("Veggie Meals:", 'rsvp-plugin'); ?> <strong><?php echo $veggieMeals[0]->veggieMealCount; ?></strong>
                        </div>
                        <div class="clear"></div>
                    </div>
                <table class="widefat post fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" id="cb" /></th>
                            <th scope="col" id="attendeeName" class="manage-column column-title" style=""><?php echo __("Attendee", 'rsvp-plugin'); ?><br />
                                <a href="admin.php?page=rsvp-top-level&amp;sort=attendee&amp;sortDirection=asc">
                                    <img src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
                                        echo((($sort == "attendee") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                        alt="Sort Ascending Attendee Status" title="Sort Ascending Attendee Status" border="0"></a> &nbsp;
                                <a href="admin.php?page=rsvp-top-level&amp;sort=attendee&amp;sortDirection=desc">
                                    <img src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
                                        echo((($sort == "attendee") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                        alt="Sort Descending Attendee Status" title="Sort Descending Attendee Status" border="0"></a>
                            </th>
                            <!--<th scope="col" id="rsvpEmail" class="manage-column column-title"><?php echo __("Email", 'rsvp-plugin'); ?></th>-->
                            <th scope="col" id="rsvpStatus" class="manage-column column-title" style=""><?php echo __("RSVP Status", 'rsvp-plugin'); ?><br />
                                <a href="admin.php?page=rsvp-top-level&amp;sort=rsvpStatus&amp;sortDirection=asc">
                                    <img src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
                                        echo((($sort == "rsvpStatus") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                        alt="Sort Ascending RSVP Status" title="Sort Ascending RSVP Status" border="0"></a> &nbsp;
                                <a href="admin.php?page=rsvp-top-level&amp;sort=rsvpStatus&amp;sortDirection=desc">
                                    <img src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
                                        echo((($sort == "rsvpStatus") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                        alt="Sort Descending RSVP Status" title="Sort Descending RSVP Status" border="0"></a>
                            </th>
                            <th scope="col" id="rsvpDate" class="manage-column column-title"><?php echo __("RSVP Date", 'rsvp-pro-plugin'); ?></th>
                            <?php if (get_option(Config::OPTION_HIDE_KIDS_MEAL) != "Y") {
                                            ?>
                            <th scope="col" id="kidsMeal" class="manage-column column-title" style=""><?php echo __("Kids Meal", 'rsvp-plugin'); ?><br />
                                    <a href="admin.php?page=rsvp-top-level&amp;sort=kidsMeal&amp;sortDirection=asc">
                                        <img src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
                                            echo((($sort == "kidsMeal") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                            alt="Sort Ascending Kids Meal Status" title="Sort Ascending Kids Meal Status" border="0"></a> &nbsp;
                                    <a href="admin.php?page=rsvp-top-level&amp;sort=kidsMeal&amp;sortDirection=desc">
                                        <img src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
                                            echo((($sort == "kidsMeal") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                            alt="Sort Descending Kids Meal Status" title="Sort Descending Kids Meal Status" border="0"></a>
                            </th>
                            <?php
                                        } ?>
                            <th scope="col" id="additionalAttendee" class="manage-column column-title" style=""><?php echo __("Additional Attendee", 'rsvp-plugin'); ?> <br />
                                        <a href="admin.php?page=rsvp-top-level&amp;sort=additional&amp;sortDirection=asc">
                                            <img src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
                                                echo((($sort == "additional") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                                alt="Sort Ascending Additional Attendees Status" title="Sort Ascending Additional Attendees Status" border="0"></a> &nbsp;
                                        <a href="admin.php?page=rsvp-top-level&amp;sort=additional&amp;sortDirection=desc">
                                            <img src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
                                                echo((($sort == "additional") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                                alt="Sort Descending Additional Attendees Status" title="Sort Descending Additional Atttendees Status" border="0"></a>
                            </th>
                            <?php if (get_option(Config::OPTION_HIDE_VEGGIE) != "Y") {
                                                    ?>
                            <th scope="col" id="veggieMeal" class="manage-column column-title" style=""><?php echo __("Vegetarian", 'rsvp-plugin'); ?> <br />
                                            <a href="admin.php?page=rsvp-top-level&amp;sort=vegetarian&amp;sortDirection=asc">
                                                <img src="<?php echo plugins_url(); ?>/rsvp/uparrow<?php
                                                    echo((($sort == "vegetarian") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                                    alt="Sort Ascending Vegetarian Status" title="Sort Ascending Vegetarian Status" border="0"></a> &nbsp;
                                            <a href="admin.php?page=rsvp-top-level&amp;sort=vegetarian&amp;sortDirection=desc">
                                                <img src="<?php echo plugins_url(); ?>/rsvp/downarrow<?php
                                                    echo((($sort == "vegetarian") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9"
                                                    alt="Sort Descending Vegetarian Status" title="Sort Descending Vegetarian Status" border="0"></a>
                            </th>
                            <?php
                                                } ?>
                            <!--<th scope="col" id="customMessage" class="manage-column column-title" style=""><?php echo __("Custom Message", 'rsvp-plugin'); ?></th>-->
                            <th scope="col" id="note" class="manage-column column-title" style=""><?php echo __("Note", 'rsvp-plugin'); ?></th>
                            <?php
                            if (get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE ) == "Y" ||
                                    get_option( Config::OPTION_CONFIG_ALLOW_PASSCODE ) == "Y" ) {
                                ?>
                                <th scope="col" id="passcode" class="manage-column column-title" style=""><?php echo __("Passcode", 'rsvp-plugin'); ?></th>
                            <?php
                            } ?>
                            <?php
                                $qRs = $this->config->GetDatabase()->get_results("SELECT id, question FROM ".$this->config->QuestionsTable()." ORDER BY sortOrder, id");
            if (count($qRs) > 0) {
                foreach ($qRs as $q) {
                    ?>
                                <th scope="col" class="manage-column -column-title"><?php echo htmlspecialchars(stripslashes($q->question)); ?></th>
                            <?php
                }
            } ?>
                            <th scope="col" id="associatedAttendees" class="manage-column column-title" style=""><?php echo __("Associated Attendees", 'rsvp-plugin'); ?></th>
                        </tr>
                    </thead>
                </table>
                <div>
                    <table class="widefat post fixed" cellspacing="0">
                    <?php
                        $i = 0;
            foreach ($attendees as $attendee) {
                ?>
                            <tr class="<?php echo(($i % 2 == 0) ? "alternate" : ""); ?> author-self">
                                <th scope="row" class="check-column"><input type="checkbox" name="attendee[]" value="<?php echo $attendee->id; ?>" /></th>
                                <td>
                                    <a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-guest&amp;id=<?php echo $attendee->id; ?>"><?php echo htmlspecialchars(stripslashes($attendee->firstName)." ".stripslashes($attendee->lastName)); ?></a>
                                </td>
                                <!--<td><?php echo htmlspecialchars(stripslashes($attendee->email)); ?></td>-->
                                <td><?php echo $attendee->rsvpStatus; ?></td>
                                <td><?php echo $attendee->rsvpDate; ?></td>
                                <?php if (get_option(Config::OPTION_HIDE_KIDS_MEAL) != "Y") {
                    ?>
                                <td><?php
                                    if ($attendee->rsvpStatus == "NoResponse") {
                                        echo "--";
                                    } else {
                                        echo(($attendee->kidsMeal == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
                                    } ?></td>
                                    <?php
                } ?>
                                <td><?php
                                    if ($attendee->rsvpStatus == "NoResponse") {
                                        echo "--";
                                    } else {
                                        echo(($attendee->additionalAttendee == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
                                    } ?></td>

                                <?php if (get_option(Config::OPTION_HIDE_VEGGIE) != "Y") {
                                        ?>
                                <td><?php
                                    if ($attendee->rsvpStatus == "NoResponse") {
                                        echo "--";
                                    } else {
                                        echo(($attendee->veggieMeal == "Y") ? __("Yes", 'rsvp-plugin') : __("No", 'rsvp-plugin'));
                                    } ?></td>
                                <?php
                                    } ?>
                                <!--<td><?php
                                    echo nl2br(stripslashes(trim($attendee->personalGreeting))); ?></td>-->
                                <td><?php echo nl2br(esc_html(stripslashes(trim($attendee->note)))); ?></td>
                                <?php
                                if (get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE ) == "Y" ||
                                get_option( Config::OPTION_CONFIG_ALLOW_PASSCODE ) == "Y" ) {
                                    ?>
                                    <td><?php echo $attendee->passcode; ?></td>
                                <?php
                                }
                $sql = "SELECT question, answer FROM ".$this->config->QuestionsTable()." q
                                        LEFT JOIN ".$this->config->AttendeeAnswersTable()." ans ON q.id = ans.questionID AND ans.attendeeID = %d
                                        ORDER BY q.sortOrder, q.id";
                $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare($sql, $attendee->id));
                if (count($aRs) > 0) {
                    foreach ($aRs as $a) {
                        ?>
                                        <td><?php echo htmlspecialchars(stripslashes($a->answer)); ?></td>
                                <?php
                    }
                } ?>
                                <td>
                                <?php
                                    $sql = "SELECT firstName, lastName FROM ".$this->config->AttendeesTable()."
                                        WHERE id IN (SELECT attendeeID FROM ".$this->config->AssociatedAttendeesTable()." WHERE associatedAttendeeID = %d)
                                            OR id in (SELECT associatedAttendeeID FROM ".$this->config->AssociatedAttendeesTable()." WHERE attendeeID = %d)";

                $associations = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare($sql, $attendee->id, $attendee->id));
                foreach ($associations as $a) {
                    echo htmlspecialchars(stripslashes($a->firstName." ".$a->lastName))."<br />";
                } ?>
                                </td>
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

        public function RenderEditGuest()   
        {
            if ((count($_POST) > 0) && !empty($_POST['firstName']) && !empty($_POST['lastName'])) {
                check_admin_referer('rsvp_add_guest');
                $passcode = (isset($_POST['passcode'])) ? $_POST['passcode'] : "";
        
                if (isset($_POST['attendeeId']) && is_numeric($_POST['attendeeId']) && ($_POST['attendeeId'] > 0)) {
                    $this->config->GetDatabase()->update(
                        $this->config->AttendeesTable(),
                                                array("firstName" => trim($_POST['firstName']),
                                                      "lastName" => trim($_POST['lastName']),
                                "email" => trim($_POST['email']),
                                                      "personalGreeting" => trim($_POST['personalGreeting']),
                                                            "rsvpStatus" => trim($_POST['rsvpStatus'])),
                                                array("id" => $_POST['attendeeId']),
                                                array("%s", "%s", "%s", "%s", "%s"),
                                                array("%d")
                    );
                    $attendeeId = $_POST['attendeeId'];
                    $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare("DELETE FROM ".$this->config->AssociatedAttendeesTable()." WHERE attendeeId = %d", $attendeeId));
                    $this->config->GetDatabase()->query($this->config->GetDatabase()->prepare("DELETE FROM ".$this->config->AssociatedAttendeesTable()." WHERE associatedAttendeeID = %d", $attendeeId));
                } else {
                    $this->config->GetDatabase()->insert(
                        $this->config->AttendeesTable(),
                        array("firstName" => trim($_POST['firstName']),
                                                         "lastName" => trim($_POST['lastName']),
                                                 "email" => trim($_POST['email']),
                                                                                             "personalGreeting" => trim($_POST['personalGreeting']),
                                                                                             "rsvpStatus" => trim($_POST['rsvpStatus'])),
                                                   array('%s', '%s', '%s', '%s', '%s')
                    );
        
                    $attendeeId = $this->config->GetDatabase()->insert_id;
                }
                if (isset($_POST['associatedAttendees']) && is_array($_POST['associatedAttendees'])) {
                    foreach ($_POST['associatedAttendees'] as $aid) {
                        if (is_numeric($aid) && ($aid > 0)) {
                            $this->config->GetDatabase()->insert($this->config->AssociatedAttendeesTable(), array("attendeeID"=>$attendeeId, "associatedAttendeeID"=>$aid), array("%d", "%d"));
                            $this->config->GetDatabase()->insert($this->config->AssociatedAttendeesTable(), array("attendeeID"=>$aid, "associatedAttendeeID"=>$attendeeId), array("%d", "%d"));
                        }
                    }
                }
        
                if (get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE ) == "Y" ||
                get_option( Config::OPTION_CONFIG_ALLOW_PASSCODE ) == "Y" ) {
                    if (empty($passcode)) {
                        $passcode = rsvp_generate_passcode();
                    }
                    if (rsvp_require_unique_passcode() && !rsvp_is_passcode_unique($passcode, $attendeeId)) {
                        $passcode = rsvp_generate_passcode();
                    }
                    $this->config->GetDatabase()->update(
                        $this->config->AttendeesTable(),
                                                array("passcode" => trim($passcode)),
                                                array("id"=>$attendeeId),
                                                array("%s"),
                                                array("%d")
                    );
                } ?>
                <p><?php echo __("Attendee", 'rsvp-plugin'); ?> <?php echo htmlspecialchars(stripslashes($_POST['firstName']." ".$_POST['lastName'])); ?> <?php echo __("has been successfully saved", 'rsvp-plugin'); ?></p>
                <p>
                    <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-top-level"><?php echo __("Continue to Attendee List", 'rsvp-plugin'); ?></a> |
                    <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-guest"><?php echo __("Add a Guest", 'rsvp-plugin'); ?></a>
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
                    $attendee = $this->config->GetDatabase()->get_row("SELECT id, firstName, lastName, email, personalGreeting, rsvpStatus, passcode FROM ".$this->config->AttendeesTable()." WHERE id = ".$_GET['id']);
                    if ($attendee != null) {
                        $attendeeId = $attendee->id;
                        $firstName = stripslashes($attendee->firstName);
                        $lastName = stripslashes($attendee->lastName);
                        $email = stripslashes($attendee->email);
                        $personalGreeting = stripslashes($attendee->personalGreeting);
                        $rsvpStatus = $attendee->rsvpStatus;
                        $passcode = stripslashes($attendee->passcode);
        
                        // Get the associated attendees and add them to an array
                        $associations = $this->config->GetDatabase()->get_results("SELECT associatedAttendeeID FROM ".$this->config->AssociatedAttendeesTable()." WHERE attendeeId = ".$attendee->id.
                                                                                             " UNION ".
                                                                                             "SELECT attendeeID FROM ".$this->config->AssociatedAttendeesTable()." WHERE associatedAttendeeID = ".$attendee->id);
                        foreach ($associations as $aId) {
                            $associatedAttendees[] = $aId->associatedAttendeeID;
                        }
                    }
                } ?>
                <form name="contact" action="admin.php?page=rsvp-admin-guest" method="post">
                    <?php wp_nonce_field('rsvp_add_guest'); ?>
            <input type="hidden" name="attendeeId" value="<?php echo $attendeeId; ?>" />
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save', 'rsvp-plugin'); ?>" />
                    </p>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="firstName"><?php echo __("First Name", 'rsvp-plugin'); ?>:</label></th>
                            <td align="left"><input type="text" name="firstName" id="firstName" size="30" value="<?php echo htmlspecialchars($firstName); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="lastName"><?php echo __("Last Name", 'rsvp-plugin'); ?>:</label></th>
                            <td align="left"><input type="text" name="lastName" id="lastName" size="30" value="<?php echo htmlspecialchars($lastName); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="email"><?php echo __("Email", 'rsvp-plugin'); ?>:</label></th>
                            <td align="left"><input type="text" name="email" id="email" size="30" value="<?php echo htmlspecialchars($email); ?>" /></td>
                        </tr>
                        <?php
                        if (get_option( Config::OPTION_CONFIG_ALLOW_USER_SEARCH_WITH_PASSCODE ) == "Y" ||
                            get_option( Config::OPTION_CONFIG_ALLOW_PASSCODE ) == "Y" ) {
                            ?>
                            <tr valign="top">
                                <th scope="row"><label for="passcode"><?php echo __("Passcode", 'rsvp-plugin'); ?>:</label></th>
                                <td align="left"><input type="text" name="passcode" id="passcode" size="30" value="<?php echo htmlspecialchars($passcode); ?>" /></td>
                            </tr>
                        <?php
                        } ?>
                        <tr>
                            <th scope="row"><label for="rsvpStatus"><?php echo __("RSVP Status", 'rsvp-plugin'); ?></label></th>
                            <td align="left">
                                <select name="rsvpStatus" id="rsvpStatus" size="1">
                                    <option value="NoResponse" <?php
                                        echo(($rsvpStatus == "NoResponse") ? " selected=\"selected\"" : ""); ?>><?php echo __("No Response", 'rsvp-plugin'); ?></option>
                                    <option value="Yes" <?php
                                        echo(($rsvpStatus == "Yes") ? " selected=\"selected\"" : ""); ?>><?php echo __("Yes", 'rsvp-plugin'); ?></option>
                                    <option value="No" <?php
                                        echo(($rsvpStatus == "No") ? " selected=\"selected\"" : ""); ?>><?php echo __("No", 'rsvp-plugin'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" valign="top"><label for="personalGreeting"><?php echo __("Custom Message", 'rsvp-plugin'); ?>:</label></th>
                            <td align="left"><textarea name="personalGreeting" id="personalGreeting" rows="5" cols="40"><?php echo htmlspecialchars($personalGreeting); ?></textarea></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __("Associated Attendees", 'rsvp-plugin'); ?>:</th>
                            <td align="left">
                                <p>
                                    <span style="margin-left: -5px;"><?php _e("Non-Associated Attendees", "rsvp-plugin"); ?></span>
                                    <span style="margin-left:26px;"><?php _e('Associated Attendees', 'rsvp-plugin'); ?></span>
                                </p>
                                <select name="associatedAttendees[]" id="associatedAttendeesSelect" multiple="multiple" size="5" style="height: 200px;">
                                    <?php
                                        $attendees = $this->config->GetDatabase()->get_results("SELECT id, firstName, lastName FROM ".$this->config->AttendeesTable()." ORDER BY lastName, firstName");
                foreach ($attendees as $a) {
                    if ($a->id != $attendeeId) {
                        ?>
                                                <option value="<?php echo $a->id; ?>"
                                                                <?php echo((in_array($a->id, $associatedAttendees)) ? "selected=\"selected\"" : ""); ?>><?php echo htmlspecialchars(stripslashes($a->firstName)." ".stripslashes($a->lastName)); ?></option>
                                    <?php
                    }
                } ?>
                                </select>
                            </td>
                        </tr>
                    <?php
                    if (($attendee != null) && ($attendee->id > 0)) {
                        $sql = "SELECT question, answer FROM ".$this->config->AttendeeAnswersTable()." ans
                            INNER JOIN ".$this->config->QuestionsTable()." q ON q.id = ans.questionID
                            WHERE attendeeID = %d
                            ORDER BY q.sortOrder";
                        $aRs = $this->config->GetDatabase()->get_results($this->config->GetDatabase()->prepare($sql, $attendee->id));
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
                            } ?>
                            </table>
                        </td>
                    </tr>
                    <?php
                        }
                    } ?>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save', 'rsvp-plugin'); ?>" />
                    </p>
                </form>
        <?php
            }
        }
    }
}
?>
