<?php
/*
Plugin Name: CGSociety Latest Posts
Plugin URI: http://kartikhariharan.com
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
//ini_set('display_errors', 'On');

foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/*.php" ) as $file ) {
    include_once $file;
}


class KhCGSocietyLatestPosts extends WP_Widget {
	
	protected $url_base;

	function __construct() {
		$this->url_base = "http://forums.cgsociety.org/";
		$params = array(
				'name' => __('CGSociety Latest Posts'),
				'description' => __('Widget to list your latest CGSociety posts.')
			);
		parent::__construct('KhCGSocietyLatestPosts', '', $params);
	}

	public function form($instance) {
		extract($instance);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id('title'); ?>"
				name="<?php echo $this->get_field_name('title'); ?>"
				value="<?php if(isset($title)) echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('userid'); ?>">User ID: </label>
			<input
				id="<?php echo $this->get_field_id('userid'); ?>"
				name="<?php echo $this->get_field_name('userid'); ?>"
				value="<?php if(isset($userid)) echo esc_attr($userid); ?>"
				size="8" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('maxposts'); ?>">Max posts to show: </label>
			<input
				id="<?php echo $this->get_field_id('maxposts'); ?>"
				name="<?php echo $this->get_field_name('maxposts'); ?>"
				value="<?php if(isset($maxposts)) echo esc_attr($maxposts); ?>"
				size="4" />
		</p>
		<?php
	}

	public function widget($args, $instance) {
		extract($args);
		extract($instance);
		echo $before_widget;

			if (empty($userid))
				return false;
			
			if (!empty($title))
				echo $before_title . $title . $after_title;
			?>
			
			<div class="media">

				<div class="media-left media-middle">
					<img class="img-thumbnail img-responsive" height="90" width="90" src="<?php echo $this->get_profile_pic_src($userid); ?>">
					<div class="small text-center text-muted">Veteran</div>
				</div>

				<div class="media-body">					
					<div>
						<h4><strong>kartikg3</strong></h4>
						<div class="small">Posts: 35</div>
						<div class="small text-muted">Join Date: Nov 2014</div>
					</div>

					<a target="_blank" href="<?php echo $this->url_base . "search.php?do=finduser&u=" . $userid; ?>" role="button" class="btn btn-success btn-sm"><img height="16" width="16" class="text-left" src="<?php echo plugins_url('images/cgs_old_logo_sm.png', __FILE__);?>"/> View Posts</a>							
				</div>						

			</div>

			<div class="container-fluid">
				<div class="row">
					<h5 class="text-uppercase">Recent Answers</h5>
				</div>
				<div class="row">
					<?php

					$link_array = $this->get_latest_posts($userid);	

					?>
					<div class="cgs-posts-list"><?php

					if (empty($maxposts))
						$maxposts = 10; // Default
					foreach(array_slice($link_array, 0, $maxposts) as $post_link) {
						extract($post_link);				
						echo '<p><a href="' . $link . '"">' . $link_text .'</a></p>';
					}

					?>
					</div>
				</div>
				<div class="row text-left"><a href="#" class="powered-by small text-muted">Powered by cgs-latest-posts</a></div>
			</div>
			<?php			

		echo $after_widget;
	}

	function get_latest_posts($userid) {

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

		return $link_array;
	}

	function get_profile_pic_src($userid) {
		$img_src = $this->url_base . "image.php?u=" . $userid;
		return $img_src;
	}

}


add_action('widgets_init', function() {
	register_widget('KhCGSocietyLatestPosts');
});

// Add the Scripts
add_action('wp_enqueue_scripts', 'add_cgs_scripts');
function add_cgs_scripts() {
	wp_enqueue_script('cgs-jq-js', plugins_url('js/jquery-1.9.1.min.js', __FILE__));
    wp_enqueue_script('cgs-bs-js', plugins_url('js/bootstrap.min.js', __FILE__));
}

// Add the Stylesheet
add_action('wp_enqueue_scripts', 'add_cgs_style');
function add_cgs_style() {
    wp_register_style('cgs-bs-style', plugins_url('css/bootstrap.min.css', __FILE__));
    wp_enqueue_style('cgs-bs-style');
    wp_register_style('cgs-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('cgs-style');
}

// Installation
//register_activation_hook(__FILE__, 'cgs_install');
function cgs_install() {
	global $wpdb;
	
	// Define the table name
	$table_name = $wpdb->prefix . 'cgs_data';

	// Set table version
	$cgs_db_version = '1.0';

	// Verify table existence
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		// Create the table
		$sql = "CREATE TABLE " . $table_name . "(

				);";
	} else {
		// Update table
		$installed_version = get_option("cgs_db_version");
		if ($installed_version != $cgs_db_version) {
			// Update db

			// update version
			update_option("cgs_db_version", $cgs_db_version);
		}

	}

	
}

?>