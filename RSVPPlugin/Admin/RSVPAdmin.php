<?php
require_once( __DIR__.'/../RSVPDatabase.php' );
require_once( __DIR__.'/../RSVPConfig.php' );
require_once( 'Home.php' );
require_once( 'Parties.php' );
require_once( 'Individuals.php' );

function rsvp_admin_configre_menu()
{
    $adminHome = add_menu_page(
        'Wedding RSVPs',
        'Wedding RSVPs',
        'publish_posts',
        'rsvp-home',
        'rsvp_admin_home',
        plugins_url("images/rsvp_lite_icon.png", __FILE__) );

    add_submenu_page(
        'rsvp-home',
        'Manage Parties',
        'Parties',
        'publish_posts',
        'rsvp-parties',
        'rsvp_admin_parties' );
        
    add_submenu_page(
        'rsvp-home',
        'Manage Individuals',
        'Individuals',
        'publish_posts',
        'rsvp-individuals',
        'rsvp_admin_individuals' );
    

}

function rsvp_admin_inject_scripts( $hook )
{
    switch ($hook) {
        case 'toplevel_page_rsvp-home':
        case 'wedding-rsvps_page_rsvp-parties':
        case 'wedding-rsvps_page_rsvp-individuals':
            wp_enqueue_style( 'w3_css_4' );
            wp_enqueue_style( 'font_awesome_5_3_1' );
            wp_enqueue_style( 'datatables_vanilla_1_6_15' );
            wp_enqueue_script( 'datatables_vanilla_1_6_15', null, null, null, true  );
            break;
        default:
            break;
    }
}

function rsvp_admin_register()
{
    add_action( 'admin_menu', 'rsvp_admin_configre_menu');
    add_action( 'admin_enqueue_scripts', 'rsvp_admin_inject_scripts' );
}
