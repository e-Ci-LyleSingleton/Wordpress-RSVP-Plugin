<?php

abstract class RSVPConfig
{
	const OPTION_NAME_CURRENT_DB_VERSION = 'rsvp_db_version';
	const SUPPORTED_DB_VERSION = '1';
	const SHORTCODE_TAG = 'wedding-rsvp-form';
	const DB_TABLE_PREFIX = 'rsvp_';
	const DB_TABLE_NAME_ATTENDEES = "rsvp_attendees";
	const DB_TABLE_NAME_PARTIES = "rsvp_parties";
	const CIPHER_KEY = '// TODO make this not so dodgy';
}

	//const OPTION_DEADLINE = "rsvp_deadline";

	//const OPTION_OPENDATE = "rsvp_opendate";

	//const OPTION_NOTIFY_EMAIL = "rsvp_notify_email_address";
	//const OPTION_NOTIFY_ON_RSVP = "rsvp_notify_when_rsvp";

?>