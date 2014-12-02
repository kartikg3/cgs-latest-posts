<?php
/*
Plugin Name: CGSociety Latest Posts
Plugin URI: https://github.com/kartikg3/cgs-latest-posts
Description: Widget to list your latest CGSociety posts.
Version: 1.0.0
Author: Kartik Hariharan
Author URI: http://kartikhariharan.com
*/

/*	Copyright 2014 Kartik Hariharan (email: kartikg3@gmail.com)
	
	Gpg4win is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Gpg4win is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
	02110-1301, USA
*/

foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/*.php" ) as $file ) {
    include_once $file;
}


class KhCGSocietyLatestPosts extends WP_Widget {
	
	protected $url_base;
	protected $min_request_interval;
	protected $show_powered_by;
	protected $powered_by_url;
	private static $instance_count = 0;
	public static $transient_key_posts_base = 'cgs-trans-posts-cache-';
	public static $transient_key_userinfo_base = 'cgs-trans-userinfo-cache-';

	function __construct() {
		$this->powered_by_url = "https://github.com/kartikg3/cgs-latest-posts";
		$this->url_base = "http://forums.cgsociety.org/";
		$this->min_request_interval = 4;
		$params = array(
				'name' => __('CGSociety Latest Posts'),
				'description' => __('Widget to list your latest CGSociety posts.')
			);
		parent::__construct('KhCGSocietyLatestPosts', '', $params);
	}

	public function form($instance) {
		extract($instance);
?>
		<h4 style="text-decoration: underline;">General Options</h4>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id('title'); ?>"
				name="<?php echo $this->get_field_name('title'); ?>"
				value="<?php if(isset($title)) echo esc_attr($title); ?>"
				placeholder="Example: My latest CGSociety Posts" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('userid'); ?>">User ID: </label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id('userid'); ?>"
				name="<?php echo $this->get_field_name('userid'); ?>"
				value="<?php if(isset($userid)) echo esc_attr($userid); ?>"
				placeholder="Example: 567472" />
		</p>
		
		<h4 style="text-decoration: underline;">Display Options</h4>
		<p>			
			<input
				id="<?php echo $this->get_field_id('show_profile_card_opt'); ?>"
				name="<?php echo $this->get_field_name('show_profile_card_opt'); ?>"
				value="<?php if(isset($show_profile_card_opt)) { echo esc_attr($show_profile_card_opt); } else {echo true;}; ?>"
				<?php if(isset($show_profile_card_opt)) { if($show_profile_card_opt == true) echo "checked"; }; ?>
				type="checkbox"
				class="checkbox"
				/>
			<label for="<?php echo $this->get_field_id('show_profile_card_opt'); ?>">Show profile card</label>
		</p>
		<p>			
			<input
				id="<?php echo $this->get_field_id('show_profile_pic'); ?>"
				name="<?php echo $this->get_field_name('show_profile_pic'); ?>"
				value="<?php if(isset($show_profile_pic)) { echo esc_attr($show_profile_pic); } else {echo true;}; ?>"
				<?php if(isset($show_profile_pic)) { if($show_profile_pic == true) echo "checked"; };?>
				type="checkbox"
				class="checkbox"
				/>
			<label for="<?php echo $this->get_field_id('show_profile_pic'); ?>">Show profile picture</label>
		</p>
		<p>			
			<input
				id="<?php echo $this->get_field_id('show_powered_by'); ?>"
				name="<?php echo $this->get_field_name('show_powered_by'); ?>"
				value="<?php if(isset($show_powered_by)) { echo esc_attr($show_powered_by); } else {echo true;}; ?>"
				<?php if(isset($show_powered_by)) { if($show_powered_by == true) echo "checked";}; ?>
				type="checkbox"
				class="checkbox"
				/>
			<label for="<?php echo $this->get_field_id('show_powered_by'); ?>">Show powered by link</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('feed_title'); ?>">Feed title: </label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id('feed_title'); ?>"
				name="<?php echo $this->get_field_name('feed_title'); ?>"
				value="<?php if(isset($feed_title)) { echo esc_attr($feed_title); } else { echo 'Recent Answers'; }; ?>"
				placeholder="Example: My Recent Posts" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('maxposts'); ?>">Max posts to show: </label>
			<input
				id="<?php echo $this->get_field_id('maxposts'); ?>"
				name="<?php echo $this->get_field_name('maxposts'); ?>"
				value="<?php if(isset($maxposts)) { echo esc_attr($maxposts); } else {echo 5;}; ?>"
				size="5" />
		</p>	


<?php
	}

	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['userid'] = strip_tags($new_instance['userid']);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['feed_title'] = strip_tags($new_instance['feed_title']);
		$instance['maxposts'] = strip_tags($new_instance['maxposts']);
		if (isset($new_instance['show_profile_card_opt']))
			$instance['show_profile_card_opt'] = $new_instance['show_profile_card_opt'];

		if (isset($new_instance['show_profile_pic']))
			$instance['show_profile_pic'] = $new_instance['show_profile_pic'];

		if (isset($new_instance['show_powered_by']))
			$instance['show_powered_by'] = $new_instance['show_powered_by'];

		delete_transient(self::$transient_key_posts_base . $instance['userid']);
		delete_transient(self::$transient_key_userinfo_base . $instance['userid']);
		return $instance;
	}

	public function widget($args, $instance) {

		extract($args);
		extract($instance);
		echo $before_widget;		

			if (empty($userid))
				return false;
			
			if (!empty($title))
				echo $before_title . $title . $after_title;

			$link_array = $this->get_latest_posts($userid);			

			if ($show_profile_card_opt) {
				if (!empty($link_array)) {
					extract($link_array[0]);
					$user_info_array = $this->get_user_info($userid, $link);
				}
			}

?>
			<div class="cgs_bs">
<?php 
				if ($show_profile_card_opt) {

?>

					<div class="media">

<?php
						if ($show_profile_pic) {
?>
							<div class="media-left media-middle">
								<img class="img-thumbnail img-responsive" height="90" width="90" src="<?php echo $this->get_profile_pic_src($userid); ?>">
								<div class="small text-center text-muted"><?php echo $user_info_array['user_status']; ?></div>
							</div>
<?php
						}
?>

						<div class="media-body">					
							<div>
								<h4><strong><?php echo $user_info_array['username']; ?></strong></h4>
								<div class="small">Posts: <?php echo $user_info_array['user_posts_count']; ?></div>
								<div class="small text-muted">Join Date: <?php echo $user_info_array['user_join_date']; ?></div>
							</div>

							<a target="_blank" href="<?php echo $this->url_base . "search.php?do=finduser&u=" . $userid; ?>" role="button" class="btn btn-success btn-sm"><img height="16" width="16" class="text-left" src="<?php echo plugins_url('images/cgs_old_logo_sm.png', __FILE__);?>"/> View Posts</a>							
						</div>						

					</div>

<?php 
				
				}
				
?>

				<div class="container-fluid">
					<div class="row">
						<h5 class="text-uppercase"><?php echo $feed_title; ?></h5>
					</div>
					<div class="row">
						
						<div class="cgs-posts-list">
<?php

						if (empty($maxposts))
							$maxposts = 10; // Default

						foreach(array_slice($link_array, 0, $maxposts) as $post_link) {
							extract($post_link);				
							echo '<p><a target="_blank" href="' . $link . '"">' . $link_text .'</a></p>';
						}

?>
						</div>
					</div>
<?php 
					if ( isset($show_powered_by) ) { 
						if($show_powered_by)
?>

							<div class="row text-left"><a target="_blank" href="<?php echo $this->powered_by_url ;?>" class="powered-by small text-muted">Powered by cgs-latest-posts</a></div>

<?php 
					} 
?>
				</div>
			</div>
<?php			

		echo $after_widget;
	}

	function get_latest_posts($userid) {

		$transient_key = self::$transient_key_posts_base . $userid;
		$link_array = get_transient($transient_key);

		if (false === $link_array) {
			
			if (self::$instance_count > 0)
				sleep($this->min_request_interval * self::$instance_count);

			self::$instance_count ++;

			$target_url = "compress.zlib://" . $this->url_base . "search.php?do=finduser&u=" . $userid;
			$html = file_get_html($target_url);

			foreach($html->find('td[class=alt1] div') as $element)	{
				$this_element = $element->children(1);
				$element_array[] = $this_element;
			}
			
			foreach(array_unique($element_array) as $post_link) {
				if(empty($post_link))
					continue;
				$link = $this->url_base . $post_link->href;
				$link_text = $post_link->nodes[0]->innertext;
				$link_array[] = array('link' => $link, 'link_text' => $link_text);			
			}

			set_transient($transient_key, $link_array, get_cache_timeout());
		}

		return $link_array;
	}

	function get_profile_pic_src($userid) {
		$img_src = $this->url_base . "image.php?u=" . $userid;
		return $img_src;
	}

	function get_user_info($userid, $link) {

		$transient_key = self::$transient_key_userinfo_base . $userid;
		$user_info_array = get_transient($transient_key);

		if (false === $user_info_array) {

			$parsed_url = parse_url($link);
			$url_parts = $parsed_url['query'];
			parse_str($url_parts, $output);
			if (empty($output['t'])){
				$t_component = $output["amp;t"];
			} else {
				$t_component = $output['t'];
			}
			$target_url = "compress.zlib://" . $this->url_base . "showthread.php?t=" . $t_component;
			$html = file_get_html($target_url);

			foreach ($html->find('a[class=bigusername]') as $element) {
				if (strstr($element->href, $userid)) {

					$username = $element->innertext;
					$user_status = $element->parent()->next_sibling()->innertext;
					$main_td = $element->parent()->parent();
					
					$td_divs = $main_td->find('div');
					$div_count = count($td_divs);
					
					$user_join_date = str_replace("Join Date: ", "", $td_divs[$div_count - 3]->nodes[0]->innertext);
					$user_posts_count = str_replace(" Posts: ", "", $td_divs[$div_count - 2]->nodes[0]->innertext);
					$user_info_array = array('username' => $username, 'user_status' => $user_status, 'user_join_date' => $user_join_date, 'user_posts_count' => $user_posts_count);
					
					set_transient($transient_key, $user_info_array, get_cache_timeout());
					break;
				}			
			}
		}		

		return $user_info_array;
	}

}

function get_cache_timeout() {
	$cache_timeout_option_key = 'cgs_cache_timeout';
	$cache_timeout = get_option($cache_timeout_option_key);	
	if (false === $cache_timeout) {
		$cache_timeout = 300; // Default cache timeout, in mins.
		update_option($cache_timeout_option_key, $cache_timeout);
	}
	return $cache_timeout * 60; // Return cache timeout in seconds.
}

// Installation
register_activation_hook(__FILE__, 'cgs_install');
function cgs_install() {
	get_cache_timeout();
}

// Register the widget
add_action('widgets_init', 'register_cgs_widget');
function register_cgs_widget() {
	register_widget('KhCGSocietyLatestPosts');
}

// Add the Scripts
add_action('wp_enqueue_scripts', 'add_cgs_scripts');
function add_cgs_scripts() {
    wp_enqueue_script('cgs-bs-js', plugins_url('js/bootstrap-cgs.min.js', __FILE__));
}

// Add the Stylesheet
add_action('wp_enqueue_scripts', 'add_cgs_style');
function add_cgs_style() {
    wp_register_style('cgs-bs-style', plugins_url('css/bootstrap-cgs.css', __FILE__));
    wp_enqueue_style('cgs-bs-style');
    wp_register_style('cgs-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('cgs-style');
}

// Add admin option page
add_action('admin_menu', 'cgs_admin_actions');
function cgs_admin_actions() {
	add_options_page('CGS Latest Posts Options', 'CGS Latest Posts Options', 'manage_options', __FILE__, 'cgs_admin_page');

	//call register settings function
	add_action('admin_init', 'register_cgs_settings');
}

// Register the settings
function register_cgs_settings() {
	register_setting( 'cgs-settings-group', 'cgs_cache_timeout', 'cgs_validate_cache_timeout' );
}


// Validation callback
function cgs_validate_cache_timeout($input) {

}

// Clear cache
function clear_cgs_cache() {
	$all_cgs_widgets = get_option('widget_KhCGSocietyLatestPosts');

	foreach($all_cgs_widgets as $cgs_widget) {
		if (empty($cgs_widget['userid'])) 
			continue;
		
		$is_posts_cache_cleared = delete_transient(KhCGSocietyLatestPosts::$transient_key_posts_base . $cgs_widget['userid']);
		$is_userinfo_cache_cleared = delete_transient(KhCGSocietyLatestPosts::$transient_key_userinfo_base . $cgs_widget['userid']);
		
		if ($is_posts_cache_cleared) {
			$success_posts_cache_array[] = $cgs_widget['userid'];
		} else {
			$failure_posts_cache_array[] = $cgs_widget['userid'];
		}

		if ($is_userinfo_cache_cleared) {
			$success_userinfo_cache_array[] = $cgs_widget['userid'];
		} else {
			$failure_userinfo_cache_array[] = $cgs_widget['userid'];
		}
	}

	if (isset($success_posts_cache_array))
		$result['success_posts_cache_array'] = $success_posts_cache_array;
	else
		$result['success_posts_cache_array'] = false;

	if (isset($failure_posts_cache_array))
		$result['failure_posts_cache_array'] = $failure_posts_cache_array;
	else
		$result['failure_posts_cache_array'] = false;

	if (isset($success_userinfo_cache_array))
		$result['success_userinfo_cache_array'] = $success_userinfo_cache_array;
	else
		$result['success_userinfo_cache_array'] = false;

	if (isset($failure_userinfo_cache_array))
		$result['failure_userinfo_cache_array'] = $failure_userinfo_cache_array;
	else
		$result['failure_userinfo_cache_array'] = false;
	
	return $result;
}

function display_cgs_clearcache_notice($success_posts_cache_array,
								       $failure_posts_cache_array,
								       $success_userinfo_cache_array,
								       $failure_userinfo_cache_array) {

	if ( !empty($success_posts_cache_array) or !empty($success_userinfo_cache_array) ) {
?>
		<div class="updated">
<?php
			if (!empty($success_posts_cache_array)) {
				foreach($success_posts_cache_array as $success) {
					echo '<p>Posts cache cleared for userid: '. $success .'.</p>';
				}
			}

			if (!empty($success_userinfo_cache_array)) {
				foreach($success_userinfo_cache_array as $success) {
					echo '<p>UserInfo cache cleared for userid: '. $success .'.</p>';
				}		
			}	
?>
		</div>
<?php
	}

	if (!empty($failure_posts_cache_array) or !empty($failure_userinfo_cache_array)) {
?>
		<div class="updated error">
<?php
			if (!empty($failure_posts_cache_array)) {
				foreach($failure_posts_cache_array as $fail) {
					echo '<p>No posts cache for userid: '. $fail .'.</p>';
				}
			}

			if (!empty($failure_userinfo_cache_array)) {
				foreach($failure_userinfo_cache_array as $fail) {
					echo '<p>No UserInfo cache for userid: '. $fail .'.</p>';
				}	
			}
?>
		</div>
<?php
	}
}

// The admin page
function cgs_admin_page() {

	$cache_timeout = get_cache_timeout();

?>
<?php
	
	if(isset($_POST['clear_cgs_cache'])) {		
		$result = clear_cgs_cache();
		extract($result);
		display_cgs_clearcache_notice($success_posts_cache_array,
								  $failure_posts_cache_array,
								  $success_userinfo_cache_array,
								  $failure_userinfo_cache_array);
	}

	screen_icon();
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>CGSociety Latest Posts Options</h2>
		<form action="options.php" method="POST">
<?php
			settings_fields('cgs-settings-group');
			do_settings_sections('cgs-settings-group');
?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="cgs_cache_timeout">Cache Timeout (in minutes): </label>
					</th>
					<td>
						<input
							id="cgs_cache_timeout"
							name="cgs_cache_timeout"
							value="<?php echo esc_attr( get_option('cgs_cache_timeout') ); ?>"
							placeholder="Example: 300"
							size="25" />
					</td>
				</tr>
				
			</table>

			<p>
				<input type="submit" name="save_cgs_options" value="Save Changes" class="button button-primary" />&nbsp;&nbsp;				
			</p>
		</form>		

		<form action="" method="post">
			<input type="hidden" name="action" value="clear_cgs_cache" />
			<input type="submit" name="clear_cgs_cache" value="Clear Cache Now" class="button button-secondary" />
		</form>
	</div>
<?php

}



?>