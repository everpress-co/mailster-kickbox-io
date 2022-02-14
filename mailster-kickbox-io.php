<?php
/*
Plugin Name: Mailster Kickbox
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=Mailster+Kickbox+Integration&utm_medium=plugin
Description: Verifies your subscribers email addresses with Kickbox
Version: 1.2
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-kickbox
License: GPLv2 or later
*/


define( 'MAILSTER_KICKBOX_VERSION', '1.2' );
define( 'MAILSTER_KICKBOX_REQUIRED_VERSION', '2.2' );
define( 'MAILSTER_KICKBOX_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/kickbox.class.php';
new MailsterKickBox();

