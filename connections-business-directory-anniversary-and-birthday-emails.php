<?php
/**
 * An extension for the Connections Business Directory plugin which adds the ability to automatically send an email to entries on their anniversary or birthday.
 *
 * @package   Connections Business Directory Extension - Anniversary and Birthday Emails
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2021 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory Extension - Anniversary and Birthday Emails
 * Plugin URI:        https://connections-pro.com/documentation/anniversary-and-birthday-emails/
 * Description:       An extension for the Connections Business Directory plugin which adds the ability to automatically send an email to entries on their anniversary or birthday.
 * Version:           1.0.4
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections-business-directory-anniversary-and-birthday-emails
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Connections_Anniversary_and_Birthday_Emails' ) ) :

	final class Connections_Anniversary_and_Birthday_Emails {

		const VERSION = '1.0.4';
		const LOG     = FALSE;

		/**
		 * Stores the instance of this class.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @var Connections_Anniversary_and_Birthday_Emails
		 */
		private static $instance;

		/**
		 * @var string The absolute path this this file.
		 *
		 * @access private
		 * @since 1.0
		 */
		private $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @access private
		 * @since 1.0
		 */
		private $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @access private
		 * @since 1.0
		 */
		private $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @access private
		 * @since 1.0
		 */
		private $basename = '';

		public function __construct() { /* Do nothing here */ }

		/**
		 * @access private
		 * @since  unknown
		 * @static
		 *
		 * @return Connections_Anniversary_and_Birthday_Emails
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Connections_Anniversary_and_Birthday_Emails ) ) {

				self::$instance = $self = new self;

				$self->file       = __FILE__;
				$self->url        = plugin_dir_url( $self->file );
				$self->path       = plugin_dir_path( $self->file );
				$self->basename   = plugin_basename( $self->file );

				$self->loadDependencies();
				$self->hooks();

				/**
				 * This should run on the `plugins_loaded` action hook. Since the extension loads on the
				 * `plugins_loaded` action hook, load immediately.
				 */
				cnText_Domain::register(
					'connections-business-directory-anniversary-and-birthday-emails',
					$self->basename,
					'load'
				);

				$self->addCronEvents();
			}

			return self::$instance;
		}

		/**
		 * Include the plugin files.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function loadDependencies() {

			//require_once( self::$path . 'includes/class.widgets.php' );
		}

		/**
		 * Register all the actions and filters.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		private function hooks() {

			// This action is ran hourly via the WP Cron.
			add_action( 'cn-aabe-hourly-event', array( __CLASS__, 'process' ) );

			// This action is ran daily via the WP Cron.
			add_action( 'cn-aabe-daily-event', array( __CLASS__, 'queue' ) );

			/*
			 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
			 */
			add_filter( 'cn_register_settings_tabs', array( __CLASS__, 'registerSettingsTab' ) );
			add_filter( 'cn_register_settings_sections', array( __CLASS__, 'registerSettingsSections' ) );
			add_filter( 'cn_register_settings_fields', array( __CLASS__, 'registerSettingsFields' ) );

			add_action( 'cn_settings_field-cn-aabe-token-description', array( __CLASS__, 'tokenDescription') );

			// Register log type.
			add_filter( 'cn_email_log_types', array( __CLASS__, 'registerEmailLogType' ) );

			// Register the log view.
			add_filter( 'cn_log_views', array( __CLASS__, 'registerLogView' ) );

			// Register the unsubscribe action.
			add_action( 'template_redirect', array( __CLASS__, 'processUnsubscribe' ) );
		}

		/**
		 * Callback for the `cn_register_settings_tabs` filter.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $tabs
		 *
		 * @return array
		 */
		public static function registerSettingsTab( $tabs ) {

			$tabs[] = array(
				'id'        => 'aabe' ,
				'position'  => 35,
				'title'     => __( 'Anniversary and Birthday Emails' , 'connections-business-directory-anniversary-and-birthday-emails' ) ,
				'page_hook' => 'connections_page_connections_settings'
			);

			return $tabs;
		}

		/**
		 * Callback for the `cn_register_settings_sections` filter.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $sections
		 *
		 * @return array
		 */
		public static function registerSettingsSections( $sections ) {

			$settings = 'connections_page_connections_settings';

			$sections[] = array(
				'plugin_id' => 'connections_aabe',
				'tab'       => 'aabe',
				'id'        => 'type',
				'position'  => 10 ,
				'title'     => __( 'Notification Types', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback'  => '',
				'page_hook' => $settings
			);

			$sections[] = array(
				'plugin_id' => 'connections_aabe',
				'tab'       => 'aabe',
				'id'        => 'notification',
				'position'  => 20 ,
				'title'     => __( 'Admin Notification', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback'  => '',
				'page_hook' => $settings
			);

			$sections[] = array(
				'plugin_id' => 'connections_aabe',
				'tab'       => 'aabe',
				'id'        => 'from',
				'position'  => 30,
				'title'     => __( 'From', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback'  => '',
				'page_hook' => $settings
			);

			$sections[] = array(
				'plugin_id' => 'connections_aabe',
				'tab'       => 'aabe',
				'id'        => 'message',
				'position'  => 40,
				'title'     => __( 'Message', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback'  => '',
				'page_hook' => $settings
			);

			$sections[] = array(
				'plugin_id' => 'connections_aabe',
				'tab'       => 'aabe',
				'id'        => 'advanced',
				'position'  => 50,
				'title'     => __( 'Advanced', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback'  => '',
				'page_hook' => $settings
			);

			return $sections;
		}

		/**
		 * Callback for the `cn_register_settings_fields` filter.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public static function registerSettingsFields( $fields ) {

			$settings     = 'connections_page_connections_settings';
			$current_user = wp_get_current_user();

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'types',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'type',
				'title'     => __( 'Send Types', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'Choose the types of email to send.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'checkbox-group',
				'options'   => array(
					'anniversary' => __( 'Anniversary', 'connections-business-directory-anniversary-and-birthday-emails' ),
					'birthday'    => __( 'Birthday', 'connections-business-directory-anniversary-and-birthday-emails' ),
				),
				'default'   => array( 'anniversary', 'birthday' ),
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'send',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => __( 'Send Notification?', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'Whether or not to send a notification that the anniversary or birthday email has been sent. It is recommended that this option is be left disabled because it you have a large directory you could receive a lot of notifications.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'checkbox',
				'default'   => 0
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'name',
				'position'  => 15,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => __( 'To Name', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'The name in which the email is to be sent to.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'default'   => __( 'Directory Administrator', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'email',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => __( 'To Email', 'connections-business-directory-anniversary-and-birthday-emails'),
				'desc'      => __( 'The address in which email is to be sent to.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'default'   => $current_user->user_email,
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'subject',
				'position'  => 25,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => __( 'Subject', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'The email subject.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'size'      => 'large',
				'default'   => __( 'Sent %fullname% a %type% email today.', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'message',
				'position'  => 30,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => __( 'Message', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => '',
				'help'      => '',
				'type'      => 'rte',
				'default'   => 'An email was sent to %fullname% congratulating them on their %type%.',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'message-token-description',
				'position'  => 30.1,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'notification',
				'title'     => '',
				'desc'      => '',
				'help'      => '',
				'type'      => 'cn-aabe-token-description',
				'default'   => '',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'name',
				'position'  => 15,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'from',
				'title'     => __( 'From Name', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'The name in which the email is said to come from.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'default'   => __( 'Directory Administrator', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'email',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'from',
				'title'     => __( 'From Email', 'connections-business-directory-anniversary-and-birthday-emails'),
				'desc'      => __( 'The address in which email is said to come from. This will set the "from" and "reply to" address.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'default'   => $current_user->user_email,
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'subject',
				'position'  => 25,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'from',
				'title'     => __( 'Subject', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'The email subject.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'text',
				'size'      => 'large',
				'default'   => __( 'Happy %type%, %fullname%!', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			$message = <<<HEREDOC
<h1>Happy %type% %fullname%!</h1>

We hope you have a great %type%!

Sincerely,

Admin from %sitetitle%.
<p style="text-align: center;">%unsubscribe%</p>
HEREDOC;

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'anniversary_message',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'message',
				'title'     => __( 'Anniversary Message', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => '',
				'help'      => '',
				'type'      => 'rte',
				'default'   => $message,
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'anniversary_message-token-description',
				'position'  => 10.1,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'message',
				'title'     => '',
				'desc'      => '',
				'help'      => '',
				'type'      => 'cn-aabe-token-description',
				'default'   => '',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'birthday_message',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'message',
				'title'     => __( 'Birthday Message', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => '',
				'help'      => '',
				'type'      => 'rte',
				'default'   => $message,
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'birthday_message-token-description',
				'position'  => 20.1,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'message',
				'title'     => '',
				'desc'      => '',
				'help'      => '',
				'type'      => 'cn-aabe-token-description',
				'default'   => '',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'days_before',
				'position'  => 10,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'advanced',
				'title'     => __( 'Number of Days', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'Enter the number of days before the event to send the email. Enter 0 to have the email sent on the day of the event.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'number',
				'size'      => 'small',
				'default'   => '0',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'start_time',
				'position'  => 20,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'advanced',
				'title'     => __( 'Time to Start', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'Specify the hour of each day in which to start sending email. Valid input is 0–23 where 8 means start sending email starting after 8:00 am.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'number',
				'size'      => 'small',
				'default'   => '2',
			);

			$fields[] = array(
				'plugin_id' => 'connections_aabe',
				'id'        => 'batch_size',
				'position'  => 30,
				'page_hook' => $settings,
				'tab'       => 'aabe',
				'section'   => 'advanced',
				'title'     => __( 'Email per Batch', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'desc'      => __( 'The number of emails to send per batch. Batches will be sent once per hour.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'help'      => '',
				'type'      => 'number',
				'size'      => 'small',
				'default'   => '20',
			);

			return $fields;
		}

		/**
		 * Callback for the `cn_email_log_types` filter.
		 *
		 * Register the `cn-aabe` log type.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $types
		 *
		 * @return array
		 */
		public static function registerEmailLogType( $types ) {

			$types['cn-form'] = array(
				'id'       => 'cn-aabe',
				'name'     => __( 'Anniversary and Birthday Email', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			return $types;
		}

		/**
		 * Callback for the `cn_log_views` filter.
		 *
		 * Register the log view.
		 *
		 * NOTE: Uses the @see cnLog_Email::viewLogs() view.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $view
		 *
		 * @return array
		 */
		public static function registerLogView( $view ) {

			$view['cn-form'] = array(
				'id'       => 'cn-aabe',
				'name'     => __( 'Anniversary and Birthday Email', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'callback' => array( 'cnLog_Email', 'viewLogs' )
			);

			return $view;
		}

		/**
		 * Callback for the `cn_email_header` filter.
		 *
		 * Add the custom email header to set the `cn-aabe` email log type.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @param array $header
		 *
		 * @return array
		 */
		public static function setEmailHeader( $header ) {

			$header[] = 'X-CN-Log-Type: cn-aabe';

			return $header;
		}

		/**
		 * Callback for the `cn-aabe-daily-event` action.
		 *
		 * This is ran daily by the WP Cron.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @return void
		 */
		public static function queue() {

			$self  = self::instance();
			$types = cnSettingsAPI::get( 'connections_aabe', 'type', 'types' );
			$days  = (int) cnSettingsAPI::get( 'connections_aabe', 'advanced', 'days_before' );
			$queue = $self->getQueue();

			$self->log( 'Updating queue.' );

			if ( ! empty( $types ) && is_array( $types ) ) {

				foreach ( $types as $type ) {

					$atts = array(
						'type'              => $type,
						'days'              => 0,
						'today'             => TRUE,
						'return'            => 'id',
						'process_user_caps' => FALSE,
						'from_timestamp'    => strtotime( "+$days days", current_time( 'timestamp' ) ),
					);

					$self->log( $atts );

					$ids = Connections_Directory()->retrieve->upcoming( $atts );

					foreach ( $ids as $id ) {

						$queue[] = array( 'id' => $id, 'type' => $type );
					}

				}
			}

			$self->log( $queue );

			$self->updateQueue( $queue );
		}

		/**
		 * Return the current queue.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return array
		 */
		public function getQueue() {

			$self  = self::instance();
			$queue = array();

			$self->log( 'Retrieving queue.' );

			$option = get_option( 'connections_aabe_queue' );

			if ( FALSE !== $option && is_array( $option ) ) {

				$queue = $option;
			}

			return $queue;
		}

		/**
		 * Update the queue array in the options table.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param $data
		 */
		public function updateQueue( $data ) {

			update_option( 'connections_aabe_queue', $data, FALSE );
		}

		/**
		 * Get a batch from the queue.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return array
		 */
		public function getBatch() {

			$self  = self::instance();
			$queue = $this->getQueue();
			$count = count( $queue );
			$batch = array();
			$limit = (int) cnSettingsAPI::get( 'connections_aabe', 'advanced', 'batch_size' );

			$self->log( 'Retrieving batch from queue.' );

			for ( $i = 0; $i < $limit && $i <= $count; $i++ ) {

				if ( empty( $queue ) ) continue;

				$batch[] = array_shift( $queue );
			}

			$this->updateQueue( $queue );

			return $batch;
		}

		/**
		 * Return the current hour (0–23).
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return int
		 */
		public function getCurrentHour() {

			$time = current_time( 'timestamp' );
			$hour = date( 'G', $time );

			if ( FALSE === $hour ) {

				$hour = date( 'G' );
			}

			return (int) $hour;
		}

		/**
		 * Whether or not the email should be sent.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return bool
		 */
		public function maybeProcessQueue() {

			$self  = self::instance();
			$send  = FALSE;
			$start = (int) cnSettingsAPI::get( 'connections_aabe', 'advanced', 'start_time' );
			$now   = self::instance()->getCurrentHour();

			$self->log( "Current hour is: {$now}" );

			if ( $start <= $now ) {

				$send = TRUE;
			}

			return $send;
		}

		/**
		 * Callback for the `cn-aabe-hourly-event` action.
		 *
		 * This is ran hourly by the WP Cron.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @return void
		 */
		public static function process() {

			$self  = self::instance();
			$do    = $self->maybeProcessQueue();

			if ( $do ) {

				$self->log( 'Processing hourly event.' );

				$queue = $self->getBatch();

				$self->log( $queue );

				foreach ( $queue as $index => $row ) {

					$self->maybeSendEmail( $row['id'], $row['type'] );
				}
			}
		}

		/**
		 * Create a token from the raw entry array data.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param mixed $data
		 *
		 * @return string
		 */
		public function createEntryToken( $data ) {

			return md5( json_encode( $data ) );
		}

		/**
		 * Send the entry event email.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param int    $id
		 * @param string $type
		 *
		 * @return bool
		 */
		public function maybeSendEmail( $id, $type ) {

			$self   = self::instance();
			$result = FALSE;

			$data = Connections_Directory()->retrieve->entry( $id );

			// Create a token from the raw entry data array.
			$token = $self->createEntryToken( $data );

			//$self->log( $data );

			$entry = new cnEntry( $data );
			$meta  = array(
				'sent'         => $entry->getMeta( array( 'key' => 'aabe_sent', 'single' => TRUE ) ),
				'unsubscribed' => $entry->getMeta( array( 'key' => 'aabe_unsubscribed', 'single' => TRUE ) ),
			);

			$self->log( $entry->getName() );
			$self->log( $entry->getEmailAddresses( array(), TRUE, TRUE ) );
			$self->log( $entry->getDates( array(), TRUE, TRUE ) );
			$self->log( $meta );

			$processed    = cnArray::get( $meta, 'sent', FALSE );
			$unsubscribed = cnArray::get( $meta, 'unsubscribed', FALSE );

			cnFormatting::toBoolean( $processed );
			cnFormatting::toBoolean( $unsubscribed );

			if ( ! $processed && ! $unsubscribed ) {

				$result = $self->sendEmail( $type, $entry, $token );

			} elseif ( $processed && ! $unsubscribed ) {

				$self->log( 'Email already sent today!' );

			} elseif ( $unsubscribed ) {

				$self->log( 'Unsubscribed, no email sent.' );
			}

			if ( $result ) {

				$self->maybeSendAdminEmail( $type, $entry, $token );
			}

			return $result;
		}

		/**
		 * @access public
		 * @since  1.0
		 *
		 * @param string  $type
		 * @param cnEntry $entry
		 * @param string  $token
		 *
		 * @return bool
		 */
		public function sendEmail( $type, $entry, $token ) {

			$self = self::instance();

			$from = cnSettingsAPI::get( 'connections_aabe', 'from' );
			$to   = $self->getEntryEmailAddress( $entry );
			$body = cnSettingsAPI::get( 'connections_aabe', 'message' );

			$self->log( "Send entry {$type} email!" );

			// Make sure we have enough info to send the email.
			if ( empty( $from['email'] ) || empty( $from['name'] ) ||
			     empty( $to ) ||
			     empty( $from['subject'] ) || empty( $body[ "{$type}_message" ] )
			) {

				$self->log( 'Required email addresses, subject or body missing, no email will be sent.' );

				return FALSE;
			}

			$self->log( 'From:' );
			$self->log( $from['email'] );
			$self->log( 'To:' );
			$self->log( $to );

			$email = new cnEmail();

			// Set email to be sent as HTML.
			$email->html();

			$email->from(
				$from['email'],
				$from['name']
			);

			/*
			 * Set Reply-To header for compatibility w/ Easy WP SMTP and Ultimate Branding.
			 * @link https://connections-pro.com/support/topic/from-email-2/#post-467625
			 *
			 * If Reply-To header is not supplied, these plugins will use the From Email Address
			 * value from their user configuration settings.
			 */
			$email->header(
				sprintf( 'Reply-To: %1$s <%2$s>', $from['name'], $from['email'] )
			);

			// Set to whom.
			$email->to(
				$to,
				$entry->getName()
			);

			// Set the subject.
			$email->subject(
				$self->replaceTokens( $type, $from['subject'], $entry, $token )
			);

			// Set the message.
			$email->message(
				$self->replaceTokens( $type, $body[ "{$type}_message" ], $entry, $token )
			);

			// Set the email template to be used.
			cnEmail_Template::template( 'default' );

			add_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

			$result = $email->send();

			$result ? $self->log( 'wp_mail() successfully sent email.' ) : $self->log( 'wp_mail() failed to send email.' );

			remove_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

			return $result;
		}

		/**
		 * Send the admin notification email.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param string  $type
		 * @param cnEntry $entry
		 * @param string  $token
		 *
		 * @return bool
		 */
		public function maybeSendAdminEmail( $type, $entry, $token ) {

			$self = self::instance();

			$notification = cnSettingsAPI::get( 'connections_aabe', 'notification' );

			cnFormatting::toBoolean( $notification['send'] );

			$self->log( 'Send admin notification email!' );

			if ( ! $notification['send'] ) return FALSE;

			if ( empty( $notification['name'] ) || empty( $notification['email'] ) ||
			     empty( $notification['subject'] ) || empty( $notification['message'] )
			) {

				$self->log( 'Required email addresses, subject or body missing, no email will be sent.' );

				return FALSE;
			}

			$email = new cnEmail();

			// Set email to be sent as HTML.
			$email->html();

			$fromName  = get_bloginfo( 'name' );
			$fromEmail = get_bloginfo( 'admin_email' );

			$email->from(
				$fromEmail,
				$fromName
			);

			/*
			 * Set Reply-To header for compatibility w/ Easy WP SMTP and Ultimate Branding.
			 * @link https://connections-pro.com/support/topic/from-email-2/#post-467625
			 *
			 * If Reply-To header is not supplied, these plugins will use the From Email Address
			 * value from their user configuration settings.
			 */
			$email->header(
				sprintf( 'Reply-To: %1$s <%2$s>', $fromName, $fromEmail )
			);

			// Set to whom.
			$email->to(
				$notification['email'],
				$notification['name']
			);

			// Set the subject.
			$email->subject(
				$self->replaceTokens( $type, $notification['subject'], $entry, $token )
			);

			// Set the message.
			$email->message(
				$self->replaceTokens( $type, $notification['message'], $entry, $token )
			);

			// Set the email template to be used.
			cnEmail_Template::template( 'default' );

			add_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

			$result = $email->send();

			$result ? $self->log( 'wp_mail() successfully sent email.' ) : $self->log( 'wp_mail() failed to send email.' );

			remove_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

			return $result;
		}

		/**
		 * Return the entry's preferred email address. If a preferred was not set,
		 * return the first if more than one was entered.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param cnEntry $entry
		 *
		 * @return string
		 */
		public function getEntryEmailAddress( $entry ) {

			// Get the preferred email address if one was set by the submitter.
			$emailAddresses = $entry->getEmailAddresses( array( 'preferred' => TRUE ), TRUE, TRUE );
			$address        = '';

			if ( ! empty( $emailAddresses ) ) {

				$address = isset( $emailAddresses[0]->address ) ? $emailAddresses[0]->address : '';
			}

			// If no preferred email address was set, grab all email addresses and then set the first email address to send the auto-reply to.
			if ( empty( $address ) ) {

				$emailAddresses = $entry->getEmailAddresses( array(), TRUE, TRUE );

				if ( ! empty( $emailAddresses ) ) {

					if ( isset( $emailAddresses[0]->address ) ) {

						$address = $emailAddresses[0]->address;
					}
				}
			}

			return $address;
		}

		/**
		 * Replace the token inn the supplied string with the entry data.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param string  $type
		 * @param string  $string
		 * @param cnEntry $entry
		 * @param string  $token
		 *
		 * @return string
		 */
		public function replaceTokens( $type, $string, $entry, $token ) {

			$search = array(
				'%sitetitle%',
				'%type%',
				'%fullname%',
				'%firstname%',
				'%lastname%',
				'%unsubscribe%',
			);

			$events = array(
				'anniversary' => __( 'Anniversary', 'connections-business-directory-anniversary-and-birthday-emails' ),
				'birthday'    => __( 'Birthday', 'connections-business-directory-anniversary-and-birthday-emails' ),
			);

			$replace = array(
				get_bloginfo('name'),
				$events[ $type ],
				$entry->getName(),
				$entry->getName( array( 'format' => '%first%' ) ),
				$entry->getName( array( 'format' => '%last%' ) ),
				$this::instance()->getUnsubscribeURL( $entry->getId(), $token ),
			);

			return str_ireplace( $search, $replace, $string );
		}

		/**
		 * Callback for the `cn_settings_field-cn-aabe-token-description` action.
		 *
		 * Display the help text below the email message body for the template token tags.
		 *
		 * @access public
		 * @since  1.0
		 * @static
		 */
		public static function tokenDescription() {
			?>
			<p>Available Email Template Tags:</p>
			<ul>
				<li><code>%sitetitle%</code> — Will be replaced with the Site Title.</li>
				<li><code>%type%</code> — Will be replaced with the event email type, eg. "Anniversary" or "Birthday".</li>
				<li><code>%fullname%</code> — Will be replaced with the entry's full name.</li>
				<li><code>%firstname%</code> — Will be replaced with the entry's first name.</li>
				<li><code>%lastname%</code> — Will be replaced with the entry's last name.</li>
				<li><code>%unsubscribe%</code> — Will be replaced with the unsubscribe link.</li>
			</ul>
			<?php
		}

		/**
		 * Create the unsubscribe link.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param int    $id
		 * @param string $token
		 *
		 * @return string
		 */
		public function getUnsubscribeURL( $id, $token ) {

			$args = array(
				'cn-aabe-action' => 'unsubscribe',
				'id'             => $id,
				'token'          => $token,
			);

			$url = add_query_arg( $args, home_url() );

			return '<a href="' . $url . '">' . __( 'To unsubscribe, click here.', 'connections-business-directory-anniversary-and-birthday-emails' ) . '</a>';
		}

		/**
		 * Callback for the `cn_aabe_unsubscribe` action.
		 *
		 * Set the entry meta which will prevent further even emails from being sent.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 */
		public static function processUnsubscribe() {

			if ( ! isset( $_GET['cn-aabe-action'] ) ) return;

			if ( ( ! isset( $_GET['id'] ) || empty( $_GET['id'] ) ) &&
			     ( ! isset( $_GET['token'] ) || empty( $_GET['token'] ) )
			) {
				return;
			}

			$data = Connections_Directory()->retrieve->entry( absint( $_GET['id'] ) );

			$token = self::instance()->createEntryToken( $data );

			if ( $token !== sanitize_text_field( $_GET['token'] ) ) return;

			$entry = new cnEntry( $data );

			cnMeta::update( 'entry', $entry->getId(), 'aabe_unsubscribed', 'true' );

			wp_die(
				__( 'You have been successfully unsubscribed anniversary and birthday emails.', 'connections-business-directory-anniversary-and-birthday-emails' ),
				__( 'Unsubscribed', 'connections-business-directory-anniversary-and-birthday-emails' ),
				200
			);
		}

		/**
		 * Callback for the plugin activation hook.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @return void
		 */
		public static function activate() {

			self::addCronEvents();
		}

		/**
		 * Callback for the plugin deactivation hook.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @return void
		 */
		public static function deactivate() {

			wp_clear_scheduled_hook( 'cn-aabe-hourly-event' );
			wp_clear_scheduled_hook( 'cn-aabe-daily-event' );
		}

		/**
		 * Add event to the WP Cron.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 *
		 * @return void
		 */
		private static function addCronEvents() {

			if ( FALSE === wp_next_scheduled( 'cn-aabe-hourly-event' ) ) {

				$date = new DateTime( 'now', cnDate::getWPTimezone() );

				/**
				 * Round up to the next hour.
				 * @link https://stackoverflow.com/a/9681317/5351316
				 */
				$minutes = $date->format( 'i' );
				$seconds = $date->format( 's' );

				if ( $minutes > 0 ) {
					$date->modify( '+1 hour' );
					$date->modify( '-' . $minutes . ' minutes' );
					$date->modify( '-' . $seconds . ' seconds' );
				}

				wp_schedule_event(
					$date->getTimestamp(),
					'hourly',
					'cn-aabe-hourly-event'
				);
			}

			/**
			 * @todo Might need to add a daily event which occurs at noon to remove/add the midnight
			 *       daily event to ensure it stays at midnight thru daylight savings time changes.
			 */
			if ( FALSE === wp_next_scheduled( 'cn-aabe-daily-event' ) ) {

				/**
				 * Schedule once daily at midnight.
				 * @link https://wordpress.stackexchange.com/a/223341
				 */
				$date = new DateTime( 'tomorrow', cnDate::getWPTimezone() );

				wp_schedule_event(
					$date->getTimestamp(),
					'daily',
					'cn-aabe-daily-event'
				);
			}
		}

		/**
		 * Just a quick and dirty way to use the PHP error log file.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @param mixed       $message
		 * @param null|string $path
		 */
		private function log( $message, $path = NULL ) {

			if ( ! self::LOG ) return;

			if ( is_string( $message ) ) {

				error_log( $message );

			} else {

				error_log( var_export( $message, TRUE ) );
			}
		}
	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return Connections_Anniversary_and_Birthday_Emails|false
	 */
	function Connections_Anniversary_and_Birthday_Emails() {

		if ( class_exists('connectionsLoad') ) {

			return Connections_Anniversary_and_Birthday_Emails::instance();

		} else {

			add_action(
				'admin_notices',
				function() {
					echo '<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Anniversary and Birthday Emails.</p></div>';
				}
			);

			return FALSE;
		}
	}

	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Anniversary_and_Birthday_Emails', 11 );

	/**
	 * Activation/Deactivation hooks.
	 *
	 * These are registered here rather than within the class because it is too late to register an activation hook
	 * after the `plugins_loaded` action has already ran.
	 */
	register_activation_hook( __FILE__, array( 'Connections_Anniversary_and_Birthday_Emails', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Connections_Anniversary_and_Birthday_Emails', 'deactivate' ) );

endif;
