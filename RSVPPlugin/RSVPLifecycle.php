<?php

require_once('RSVPConfig.php');

function rsvp_lifecycle_register_plugin()
{
    add_action('init', 'rsvp_lifecycle_init');
    register_activation_hook(__FILE__, 'rsvp_lifecycle_on_activate');
}

function rsvp_lifecycle_init()
{    
    wp_register_style( 'w3_css_4', plugins_url("rsvp/assets/css/w3.css", "rsvp") );
    wp_register_style('font_awesome_5_3_1', "//use.fontawesome.com/releases/v5.3.1/css/all.css");    
}

function rsvp_database_v1_upgrade($db)
{
    $table = RSVPConfig::DB_TABLE_NAME_PARTIES;
    if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "CREATE TABLE `$table` (
            `partyId` int(11) NOT NULL AUTO_INCREMENT,
            `partyName` varchar(32) DEFAULT NULL,
            PRIMARY KEY (`partyId`) USING BTREE
            );";
        $db->query($sql);
    }

    $table = RSVPConfig::DB_TABLE_NAME_ATTENDEES;
    if ($db->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "CREATE TABLE `$table` (
            `attendeeId` int(11) NOT NULL AUTO_INCREMENT,
            `firstName` varchar(255) NOT NULL,
            `lastName` varchar(255) NOT NULL,
            `attendance` BIT(1) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `phone` varchar(16) DEFAULT NULL,
            `street` varchar(255) DEFAULT NULL,
            `city` varchar(255) DEFAULT NULL,
            `postcode` varchar(8) DEFAULT NULL,
            `beverageOptions` enum('alcoholic','non-alcoholic') DEFAULT NULL,
            `mealOptions` enum('adult','child') DEFAULT NULL,
            `dietaryReqs` enum('glutenfree','vegetarian','vegan','other') DEFAULT NULL,
            `otherDietaryReqs` varchar(2048) NULL,
            `songRequest` varchar(255) DEFAULT NULL,
            `attendanceNotes` varchar(2048) NULL,
            PRIMARY KEY (`attendeeId`),
            KEY `partyId` (`partyId`),
            CONSTRAINT `fk_wp_rsvp_parties` FOREIGN KEY (`partyId`) REFERENCES `wp_rsvp_partyId` (`partyId`) ON DELETE SET NULL
            );";
        $db->query($sql);

    }
        
    // Fast forward to version 1
    update_option(RSVPConfig::OPTION_NAME_CURRENT_DB_VERSION, "1");
}

function rsvp_lifecycle_on_activate()
{
    register_setting("rsvp-option-group", RSVPConfig::OPTION_NAME_CURRENT_DB_VERSION);

    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    $installed_ver = get_option(RSVPConfig::OPTION_NAME_CURRENT_DB_VERSION);
    
    switch ($installed_ver) {
        case null:
            add_option(RSVPConfig::OPTION_NAME_CURRENT_DB_VERSION, "0");
        case '0':
            rsvp_database_v1_upgrade( $wpdb );
        case '1':
            break;
        default:
            throw "Unknown database version exception";
            break;
    }
}
