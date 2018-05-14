<?php
/**
 *
 * @package rsvp
 * @author Swim or Die Software
 * @version 2.4.2
 */
/*
 * Plugin Name: RSVP
 * Text Domain: rsvp-plugin
 * Plugin URI: http://wordpress.org/extend/plugins/rsvp/
 * Description: This plugin allows guests to RSVP to an event. It was made
 * initially for weddings but could be used for other things.
 * Author: Swim or Die Software
 * Version: 2.4.2
 * Author URI: http://www.swimordiesoftware.com
 * License: GPL
 */
//
// INSTALLATION: see readme.txt
//
// USAGE: Once the RSVP plugin has been installed, you can set the custom text
// via Settings -> RSVP Options in the admin area.
//
// To add, edit, delete and see rsvp status there will be a new RSVP admin
// area just go there.
//
// To allow people to rsvp create a new page and add "[rsvp]" to the text

use RSVPPlugin\Config;
use RSVPPlugin\Lifecyle;
use RSVPPlugin\AdminController;
use RSVPPlugin\ClientController;

require_once 'RSVPPlugin/Config.php';
require_once 'RSVPPlugin/Lifecycle.php';
require_once 'RSVPPlugin/Admin/Controllers/Admin.php';
require_once 'RSVPPlugin/Client/Controllers/ClientController.php';

$config = new Config();
$lifecycle = new Lifecyle($config);
$wpAdmin = new AdminController($config);
$wpClient = new ClientController($config);

?>
