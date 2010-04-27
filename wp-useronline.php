<?php
/*
Plugin Name: WP-UserOnline
Plugin URI: http://wordpress.org/extend/plugins/wp-useronline/
Description: Enable you to display how many users are online on your Wordpress blog with detailed statistics of where they are and who there are(Members/Guests/Search Bots).
Version: 2.70-alpha3 (very buggy)
Author: Lester 'GaMerZ' Chan & scribu


Copyright 2009  Lester Chan  (email : lesterchan@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class UserOnline_Core {
	static $options;
	static $most;
	static $naming;
	static $templates;

	private static $useronline;

	function get_user_online_count() {
		global $wpdb;

		if ( is_null(self::$useronline) )
			self::$useronline = intval($wpdb->get_var("SELECT COUNT(*) FROM $wpdb->useronline"));

		return self::$useronline;
	}

	function init() {
		add_action('plugins_loaded', array(__CLASS__, 'wp_stats_integration'));

		add_action('template_redirect', array(__CLASS__, 'scripts'));

		add_action('admin_head', array(__CLASS__, 'record'));
		add_action('wp_head', array(__CLASS__, 'record'));

		add_action('wp_ajax_useronline', array(__CLASS__, 'ajax'));
		add_action('wp_ajax_nopriv_useronline', array(__CLASS__, 'ajax'));

		add_shortcode('page_useronline', 'users_online_page');

		register_activation_hook(__FILE__, array(__CLASS__, 'upgrade'));

		// Table
		new scbTable('useronline', __FILE__, "
			timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			user_type varchar(20) NOT NULL default 'guest',
			user_id bigint(20) NOT NULL default 0,
			user_name varchar(250) NOT NULL default '',
			user_ip varchar(20) NOT NULL default '',
			user_agent varchar(255) NOT NULL default '',
			page_title text NOT NULL default '',
			page_url varchar(255) NOT NULL default '',
			referral varchar(255) NOT NULL default '',
			UNIQUE KEY useronline_id (timestamp, user_ip, user_agent)
		");

		self::$most = new scbOptions('useronline_most', __FILE__, array(
			'count' => 1,
			'date' => current_time('mysql')
		));

		self::$options = new scbOptions('useronline', __FILE__, array(
			'timeout' => 300,
			'url' => trailingslashit(get_bloginfo('url')) . 'useronline'
		));

		self::$naming = new scbOptions('useronline_naming', __FILE__, array(
			'user'		=> __('1 User', 'wp-useronline'), 
			'users'		=> __('%COUNT% Users', 'wp-useronline'), 
			'member'	=> __('1 Member', 'wp-useronline'), 
			'members'	=> __('%COUNT% Members', 'wp-useronline'), 
			'guest' 	=> __('1 Guest', 'wp-useronline'),
			'guests'	=> __('%COUNT% Guests', 'wp-useronline'),
			'bot'		=> __('1 Bot', 'wp-useronline'),
			'bots'		=> __('%COUNT% Bots', 'wp-useronline')
		));

		self::$templates = new scbOptions('useronline_templates', __FILE__, array(
			'useronline' => '<a href="%PAGE_URL%"><strong>%USERS%</strong> '.__('Online', 'wp-useronline').'</a>',
			'browsingsite' => array(
				__(',', 'wp-useronline').' ',
				__(',', 'wp-useronline').' ', 
				__(',', 'wp-useronline').' ', 
				_x('Users', 'Template Element', 'wp-useronline').': <strong>%MEMBER_NAMES%%GUESTS_SEPERATOR%%GUESTS%%BOTS_SEPERATOR%%BOTS%</strong>'
			),
			'browsingpage' => array(
				__(',', 'wp-useronline').' ',
				__(',', 'wp-useronline').' ',
				__(',', 'wp-useronline').' ', 
				'<strong>%USERS%</strong> '.__('Browsing This Page.', 'wp-useronline').'<br />'._x('Users', 'Template Element', 'wp-useronline').': <strong>%MEMBER_NAMES%%GUESTS_SEPERATOR%%GUESTS%%BOTS_SEPERATOR%%BOTS%</strong>'
			)
		));
	}

	function upgrade() {
		global $wpdb;

		$r = $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}useronline");

		// todo
	}

	function scripts() {
		$js_dev = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';

		wp_enqueue_script('wp-useronline', plugins_url("useronline$js_dev.js", __FILE__), array('jquery'), '2.70', true);
		wp_localize_script('wp-useronline', 'useronlineL10n', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'timeout' => get_option('useronline_timeout')*1000
		));
	}

	function record() {
		global $wpdb;

		$user_ip = self::get_ip();
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$page_url = $_SERVER['REQUEST_URI'];

		$referral = strip_tags(@$_SERVER['HTTP_REFERER']);

		$current_user = wp_get_current_user();

		// Check For Bot
		$bots = array('Google Bot' => 'googlebot', 'Google Bot' => 'google', 'MSN' => 'msnbot', 'Alex' => 'ia_archiver', 'Lycos' => 'lycos', 'Ask Jeeves' => 'jeeves', 'Altavista' => 'scooter', 'AllTheWeb' => 'fast-webcrawler', 'Inktomi' => 'slurp@inktomi', 'Turnitin.com' => 'turnitinbot', 'Technorati' => 'technorati', 'Yahoo' => 'yahoo', 'Findexa' => 'findexa', 'NextLinks' => 'findlinks', 'Gais' => 'gaisbo', 'WiseNut' => 'zyborg', 'WhoisSource' => 'surveybot', 'Bloglines' => 'bloglines', 'BlogSearch' => 'blogsearch', 'PubSub' => 'pubsub', 'Syndic8' => 'syndic8', 'RadioUserland' => 'userland', 'Gigabot' => 'gigabot', 'Become.com' => 'become.com', 'Baidu' => 'baidu');

		$bot_found = false;
		foreach ( $bots as $name => $lookfor )
			if ( stristr($user_agent, $lookfor) !== false ) {
				$user_id = 0;
				$user_name = $name;
				$username = $lookfor;
				$user_type = 'bot';
				$bot_found = true;

				break;
			}

		$where = $wpdb->prepare("WHERE user_ip = %s", $user_ip);

		// If No Bot Is Found, Then We Check Members And Guests
		if ( !$bot_found ) {
			// Check For Member
			if ( $current_user->ID ) {
				$user_id = $current_user->ID;
				$user_name = $current_user->display_name;
				$user_type = 'member';
				$where = $wpdb->prepare("WHERE user_id = %d", $user_id);
			// Check For Comment Author (Guest)
			} elseif ( !empty($_COOKIE['comment_author_'.COOKIEHASH]) ) {
				$user_id = 0;
				$user_name = trim($_COOKIE['comment_author_'.COOKIEHASH]);
				$user_type = 'guest';
			// Check For Guest
			} else {
				$user_id = 0;
				$user_name = __('Guest', 'wp-useronline');
				$user_type = 'guest';
			}
		}

		// Check For Page Title
		if ( is_admin() && function_exists('get_admin_page_title') ) {
			$page_title = ' &raquo; ' . __('Admin', 'wp-useronline') . ' &raquo; ' . get_admin_page_title();
		} else {
			$page_title = wp_title('&raquo;', false);
			if ( empty($page_title) )
				$page_title = ' &raquo; ' . $_SERVER['REQUEST_URI'];
			elseif ( is_singular() )
				$page_title = ' &raquo; ' . __('Archive', 'wp-useronline') . ' ' . $page_title;
		}
		$page_title = get_bloginfo('name') . $page_title;

		// Delete Users
		$delete_users = $wpdb->query($wpdb->prepare("
			DELETE FROM $wpdb->useronline 
			$where OR timestamp < CURRENT_TIMESTAMP - %d
		", self::$options->timeout));

		// Insert Users
		$data = compact('user_type', 'user_id', 'user_name', 'user_ip', 'user_agent', 'page_title', 'page_url', 'referral');
		$data = stripslashes_deep($data);
		$insert_user = $wpdb->insert($wpdb->useronline, $data);

		// Count Users Online
		self::$useronline = intval($wpdb->get_var("SELECT COUNT(*) FROM $wpdb->useronline"));

		// Maybe Update Most User Online
		if ( self::$useronline > self::$most->count )
			self::$most->update(array(
				'count' => self::$useronline,
				'date' => current_time('mysql')
			));
	}

	function ajax() {
		$mode = trim($_POST['mode']);

		switch($mode) {
			case 'count':
				users_online();
				break;
			case 'browsingsite':
				users_browsing_site();				
				break;
			case 'browsingpage':
				users_browsing_page();
				break;
		}

		die();
	}

	function wp_stats_integration() {
		if ( function_exists('stats_page') )
			require_once dirname(__FILE__) . '/wp-stats.php';
	}

	private function get_ip() {
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else
			$ip_address = $_SERVER["REMOTE_ADDR"];

		list($ip_address) = explode(',', $ip_address);

		return $ip_address;
	}

	private function clear_table() {
		global $wpdb;

		$wpdb->query("DELETE FROM $wpdb->useronline");
	}
}

/*
### Function: Update Member last Visit
//add_action('wp_head', 'update_memberlastvisit');
function update_memberlastvisit() {
	global $current_user, $user_ID;
	if ( !empty($current_user ) && is_user_logged_in()) {
		update_user_option($user_ID, 'member_last_login', current_time('timestamp'));   
	}
}


### Function: Get Member last Visit
function get_memberlastvisit($user_id = 0) {
	return UserOnline_Template::format_date(get_user_option('member_last_login', $user_id));
}
*/

function _useronline_init() {
	require_once dirname(__FILE__) . '/scb/load.php';

	require_once dirname(__FILE__) . '/template-tags.php';
	require_once dirname(__FILE__) . '/deprecated.php';

	load_plugin_textdomain('wp-useronline', '', basename(dirname(__FILE__)));

	UserOnline_Core::init();

	require_once dirname(__FILE__) . '/widget.php';
	scbWidget::init('UserOnline_Widget', __FILE__, 'useronline');

	if ( function_exists('stats_page') )
		require_once dirname(__FILE__) . '/wp-stats.php';

	if ( is_admin() ) {
		require_once dirname(__FILE__) . '/admin.php';
		scbAdminPage::register('UserOnline_Options', __FILE__);
		scbAdminPage::register('UserOnline_Admin_Page', __FILE__);
	}
}
_useronline_init();

function wpu_linked_names($name, $user) {
#debug_print_backtrace();
	if ( !$user->user_id )
		return $name;

	return html_link(get_author_posts_url($user->user_id), $name);
}
add_filter('useronline_display_user', 'wpu_linked_names', 10, 2);

