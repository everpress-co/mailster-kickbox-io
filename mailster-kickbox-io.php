<?php
/*
Plugin Name: Mailster Kickbox
Requires Plugins: mailster
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=Kickbox
Description: Verifies your subscribers email addresses with Kickbox
Version: 1.2.0
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-kickbox
License: GPLv2 or later
*/


define( 'MAILSTER_KICKBOX_VERSION', '1.2.0' );
define( 'MAILSTER_KICKBOX_REQUIRED_VERSION', '4.0' );
define( 'MAILSTER_KICKBOX_FILE', __FILE__ );

require_once __DIR__ . '/classes/kickbox.class.php';
new MailsterKickBox();
