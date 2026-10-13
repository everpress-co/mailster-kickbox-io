<?php
/**
 * Mailster Kickbox integration class.
 *
 * @package Mailster_Kickbox
 */

/**
 * Verifies subscriber email addresses via the Kickbox API.
 *
 * @since 1.0
 */
class MailsterKickBox {

	/**
	 * Absolute filesystem path to the plugin directory.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * URL to the plugin directory.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Sets up paths, activation hooks, and init callbacks.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_KICKBOX_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_KICKBOX_FILE );

		register_activation_hook( MAILSTER_KICKBOX_FILE, array( &$this, 'activate' ) );
		register_deactivation_hook( MAILSTER_KICKBOX_FILE, array( &$this, 'deactivate' ) );

		load_plugin_textdomain( 'mailster-kickbox' );

		add_action( 'init', array( &$this, 'init' ), 1 );
	}

	/**
	 * Seeds default Kickbox options and shows a setup notice on activation.
	 *
	 * @since 1.0
	 *
	 * @param bool $network_wide Optional. Whether the plugin is network-activated. Default false.
	 * @return void
	 */
	public function activate( $network_wide ) {

		unset( $network_wide );

		if ( function_exists( 'mailster' ) ) {

			/* translators: %s: link to the Kickbox settings page */
			mailster_notice( sprintf( __( 'Define your Kickbox options on the %s!', 'mailster-kickbox' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=kickbox#kickbox">Settings Page</a>' ), '', false, 'kickbox' );

			$defaults = array(
				'kickbox_timeout'        => 6000,
				'kickbox_response'       => array( 'deliverable', 'risky', 'unknown' ),
				'kickbox_reasons'        => array( 'invalid_domain', 'rejected_email' ),
				'kickbox_role'           => true,
				'kickbox_free'           => true,
				'kickbox_accept_all'     => true,
				'kickbox_response_error' => __( 'Sorry, your email address is not accepted!', 'mailster-kickbox' ),
				'kickbox_reasons_error'  => __( 'Sorry, your email address is not accepted!', 'mailster-kickbox' ),
				'kickbox_error'          => __( 'Sorry, your email address is not accepted!', 'mailster-kickbox' ),
				'kickbox_sendex'         => 0.4,
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}
		}
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @since 1.0
	 *
	 * @param bool $network_wide Optional. Whether the plugin was network-activated. Default false.
	 * @return void
	 */
	public function deactivate( $network_wide ) {
		unset( $network_wide );
	}

	/**
	 * Registers admin settings and subscriber verification when Mailster is active.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function init() {

		if ( ! function_exists( 'mailster' ) ) {

			add_action( 'admin_notices', array( $this, 'notice' ) );

		} else {

			if ( is_admin() ) {

				add_filter( 'mailster_setting_sections', array( &$this, 'settings_tab' ) );
				add_action( 'mailster_section_tab_kickbox', array( &$this, 'settings' ) );

			}

			add_action( 'mailster_verify_subscriber', array( $this, 'verify_subscriber' ) );

		}
	}

	/**
	 * Verifies a subscriber entry email against Kickbox before it is saved.
	 *
	 * Skips verification during bulk import unless the import option is enabled.
	 *
	 * @since 1.0
	 *
	 * @param array $entry Subscriber entry data.
	 * @return array|WP_Error Unmodified entry on success, or a WP_Error on rejection.
	 */
	public function verify_subscriber( $entry ) {

		if ( ! isset( $entry['email'] ) ) {
			return $entry;
		}
		if ( ! mailster_option( 'kickbox_import' ) && defined( 'MAILSTER_DO_BULKIMPORT' ) && MAILSTER_DO_BULKIMPORT ) {
			return $entry;
		}

		$is_valid = $this->verify( $entry['email'] );
		if ( is_wp_error( $is_valid ) ) {
			return $is_valid;
		}

		return $entry;
	}

	/**
	 * Calls the Kickbox API and applies configured acceptance rules.
	 *
	 * @since 1.0
	 *
	 * @param string $email Email address to verify.
	 * @return bool|WP_Error True when accepted, or a WP_Error when rejected by a rule.
	 */
	public function verify( $email ) {

		$endpoint = 'https://api.kickbox.com/v2/verify';

		$url = add_query_arg(
			array(
				'email'   => rawurlencode( $email ),
				'apikey'  => mailster_option( 'kickbox_apikey' ),
				'timeout' => mailster_option( 'kickbox_timeout' ),
			),
			$endpoint
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => ( mailster_option( 'kickbox_timeout' ) / 1000 ) + 3,
			)
		);

		$body    = wp_remote_retrieve_body( $response );
		$headers = wp_remote_retrieve_headers( $response );

		if ( isset( $headers['x-kickbox-balance'] ) ) {
			mailster_update_option( 'kickbox_balance', $headers['x-kickbox-balance'] );
		}

		$result = json_decode( $body );

		if ( ! $result->success ) {
			/* translators: %s: Kickbox API error message */
			mailster_notice( '<strong>' . sprintf( __( 'There was an error while verifying an email address via kickbox.com: %s', 'mailster-kickbox' ), $result->message ) . '</strong>', 'error', false, 'kickboxerror' );
			return true;
		}

		// General acceptation.
		if ( ! in_array( $result->result, mailster_option( 'kickbox_response', array() ), true ) ) {
			return new WP_Error( 'kickbox_response', mailster_option( 'kickbox_response_error' ), 'email' );
		}

		// Reasons specific rejection.
		if ( in_array( $result->reason, mailster_option( 'kickbox_reasons', array() ), true ) ) {
			return new WP_Error( 'kickbox_reasons', mailster_option( 'kickbox_reasons_error' ), 'email' );
		}

		// Special rejections.
		if ( $result->role && ! mailster_option( 'kickbox_role' ) ) {
			return new WP_Error( 'kickbox_role', mailster_option( 'kickbox_error' ), 'email' );
		}

		if ( $result->free && ! mailster_option( 'kickbox_free' ) ) {
			return new WP_Error( 'kickbox_free', mailster_option( 'kickbox_error' ), 'email' );
		}

		if ( $result->disposable && ! mailster_option( 'kickbox_disposable' ) ) {
			return new WP_Error( 'kickbox_disposable', mailster_option( 'kickbox_error' ), 'email' );
		}

		if ( $result->accept_all && ! mailster_option( 'kickbox_accept_all' ) ) {
			return new WP_Error( 'kickbox_accept_all', mailster_option( 'kickbox_error' ), 'email' );
		}

		if ( $result->sendex < mailster_option( 'kickbox_sendex' ) ) {
			return new WP_Error( 'kickbox_sendex', mailster_option( 'kickbox_error' ), 'email' );
		}

		return true;
	}

	/**
	 * Inserts the Kickbox settings section into Mailster settings tabs.
	 *
	 * @since 1.0
	 *
	 * @param array $settings Existing settings section map.
	 * @return array Modified settings sections including Kickbox.
	 */
	public function settings_tab( $settings ) {

		$position = 3;
		$settings = array_slice( $settings, 0, $position, true ) +
					array( 'kickbox' => 'Kickbox' ) +
					array_slice( $settings, $position, null, true );

		return $settings;
	}

	/**
	 * Renders the Kickbox settings section fields.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function settings() {

		?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'API Key', 'mailster-kickbox' ); ?></th>
			<td><p><input type="text" name="mailster_options[kickbox_apikey]" value="<?php echo esc_attr( mailster_option( 'kickbox_apikey' ) ); ?>" class="large-text"></p></td>
		</tr>
		<?php if ( null !== mailster_option( 'kickbox_balance' ) ) : ?>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Balance', 'mailster-kickbox' ); ?></th>
			<td><input type="hidden" name="mailster_options[kickbox_balance]" value="<?php echo esc_attr( mailster_option( 'kickbox_balance' ) ); ?>"><p>
			<?php
			printf(
				/* translators: %d: remaining Kickbox API credits */
				esc_html__( 'You have %d credits left', 'mailster-kickbox' ),
				(int) mailster_option( 'kickbox_balance' )
			);
			?>
			</p></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Timeout', 'mailster-kickbox' ); ?></th>
			<td><p><input type="text" name="mailster_options[kickbox_timeout]" value="<?php echo esc_attr( mailster_option( 'kickbox_timeout' ) ); ?>" class="small-text"> Milliseconds</p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Import', 'mailster-kickbox' ); ?></th>
			<td><p><label><input type="hidden" name="mailster_options[kickbox_import]" value=""><input type="checkbox" name="mailster_options[kickbox_import]" value="1" <?php checked( mailster_option( 'kickbox_import' ) ); ?>> use for import</label></p>
			<p class="description">This will significantly decrease import time because for every subscriber WordPress needs to verify the email on the kickbox.com server. It's better to use the <a href="https://kickbox.com/app/verify" class="external">list verification</a> to verify large lists.</p>
				</td>
		</tr>
	</table>
	<h3>Rules</h3>
	<p class="description">You can define rules when you accept an email address and when you don't. All rules are based on the API response by kickbox.com. Please check their <a href="http://docs.kickbox.com/docs/using-the-api" class="external">API documentation</a> for more info.</p>
	<p class="description">By default the given options are fine and can be kept. If you have special needs feel free to adopt them.</p>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Accept email if response is', 'mailster-kickbox' ); ?></th>
			<td>
		<?php
		$reasons = array(
			'deliverable'   => 'deliverable',
			'undeliverable' => 'undeliverable',
			'risky'         => 'risky',
			'unknown'       => 'unknown',
		);

		$checked = mailster_option( 'kickbox_response', array() );
		foreach ( $reasons as $code => $reason ) {
			echo '<p><label><input type="checkbox" name="mailster_options[kickbox_response][]" value="' . esc_attr( $code ) . '" ' . checked( in_array( $code, $checked, true ), true, false ) . '><code>[' . esc_html( $code ) . ']</code> ' . esc_attr( $reason ) . '</label></p>';
		}
		?>
			<p><strong><?php esc_html_e( 'Error Message if rule doesn\'t match', 'mailster-kickbox' ); ?></strong>
			<input type="text" name="mailster_options[kickbox_response_error]" value="<?php echo esc_attr( mailster_option( 'kickbox_response_error' ) ); ?>" class="large-text"></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Reject email if reason is', 'mailster-kickbox' ); ?></th>
			<td>
		<?php

		$reasons = array(
			'invalid_domain'     => 'Domain for email does not exist',
			'rejected_email'     => 'Email address was rejected by the SMTP server, email address does not exist',
			'accepted_email'     => 'Email address was accepted by the SMTP server',
			'low_quality'        => 'Email address has quality issues that may make it a risky or low-value address',
			'low_deliverability' => 'Email address appears to be deliverable, but deliverability cannot be guaranteed',
			'no_connect'         => 'Could not connect to SMTP server',
			'timeout'            => 'SMTP session timed out',
			'invalid_smtp'       => 'SMTP server returned an unexpected/invalid response',
			'unavailable_smtp'   => 'SMTP server was unavailable to process our request',
			'unexpected_error'   => 'An unexpected error has occurred',
		);

		$checked = mailster_option( 'kickbox_reasons', array() );
		foreach ( $reasons as $code => $reason ) {
			echo '<p><label><input type="checkbox" name="mailster_options[kickbox_reasons][]" value="' . esc_attr( $code ) . '" ' . checked( in_array( $code, $checked, true ), true, false ) . '><code>[' . esc_html( $code ) . ']</code> ' . esc_attr( $reason ) . '</label></p>';
		}
		?>
			<p><strong><?php esc_html_e( 'Error Message if rule doesn\'t match', 'mailster-kickbox' ); ?></strong>
			<input type="text" name="mailster_options[kickbox_reasons_error]" value="<?php echo esc_attr( mailster_option( 'kickbox_reasons_error' ) ); ?>" class="large-text"></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Accept email address if', 'mailster-kickbox' ); ?></th>
			<td>
			<p><label><input type="checkbox" name="mailster_options[kickbox_role]" value="1" <?php checked( mailster_option( 'kickbox_role' ) ); ?>> is a role address (postmaster@example.com, support@example.com, etc)</label></p>
			<p><label><input type="checkbox" name="mailster_options[kickbox_free]" value="1" <?php checked( mailster_option( 'kickbox_free' ) ); ?>> uses a free email service like gmail.com or yahoo.com.</label></p>
			<p><label><input type="checkbox" name="mailster_options[kickbox_disposable]" value="1" <?php checked( mailster_option( 'kickbox_disposable' ) ); ?>> uses a disposable domain like trashmail.com or mailinator.com.</label></p>
			<p><label><input type="checkbox" name="mailster_options[kickbox_accept_all]" value="1" <?php checked( mailster_option( 'kickbox_accept_all' ) ); ?>>was accepted, but the domain appears to accept all emails addressed to that domain</label></p>
			<p><a href="https://docs.kickbox.com/docs/the-sendex" class="external">Sendex score</a> is at least<input type="text" name="mailster_options[kickbox_sendex]" value="<?php echo esc_attr( floatval( mailster_option( 'kickbox_sendex' ) ) ); ?>" class="small-text"></p>

			<p><strong><?php esc_html_e( 'Error Message if rule doesn\'t match', 'mailster-kickbox' ); ?></strong>
			<input type="text" name="mailster_options[kickbox_error]" value="<?php echo esc_attr( mailster_option( 'kickbox_error' ) ); ?>" class="large-text"></p>
			</td>
			</td>
		</tr>
	</table>

		<?php
	}

	/**
	 * Prints an admin notice when Mailster is missing or too old.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function notice() {
		?>
	<div id="message" class="error">
		<p>
			<strong>Kickbox for Mailster</strong> requires the <a href="https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=Kickbox">Mailster Newsletter Plugin</a>, at least version <strong><?php echo esc_html( MAILSTER_KICKBOX_REQUIRED_VERSION ); ?></strong>. Plugin deactivated.
		</p>
	</div>
		<?php
	}
}
