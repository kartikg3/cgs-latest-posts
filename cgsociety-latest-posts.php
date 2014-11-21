<?php
/*
Plugin Name: CGSociety Latest Posts
Plugin URI: http://kartikhariharan.com
Description: Widget to list your latest CGSociety posts.
Version: 1.0.0
Author: Kartik Hariharan
Author URI: http://kartikhariharan.com
License: none
*/
//ini_set('display_errors', 'On');
include_once('simple_html_dom.php');

foreach ( glob( plugin_dir_path( __FILE__ ) . "includes/*.php" ) as $file ) {
    include_once $file;
}
class kh_cgsociety_latest_posts extends WP_Widget {
	function __construct() {
		$params = array(
				'name' => __('CGSociety Latest Posts'),
				'description' => __('Widget to list your latest CGSociety posts.')
			);
		parent::__construct('kh_cgsociety_latest_posts', '', $params);
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
		<?php
	}

	public function widget($args, $instance) {
		extract($args);
		extract($instance);
		echo $before_widget;
			echo $before_title . $title . $after_title;
			$url_base = "http://forums.cgsociety.org/";
			$target_url = "http://forums.cgsociety.org/search.php?do=finduser&u=" . $userid;
			echo $target_url;
			$html = file_get_html($target_url);
			foreach($html->find('td.alt1') as $element)	{
				$link_array[] = $element->children(1)->find('a')[0];
				//$link_array[] = $element->children(1)->next_sibling();
   			}
   			?>
   			<ul>
   			<?php
   			foreach(array_unique($link_array) as $post_link) {
   				echo "<li>" . $post_link . "</li>";
   			}
   			?>
   			</ul>
   			<?php

		echo $after_widget;
	}
}

add_action('widgets_init', function() {
	register_widget('kh_cgsociety_latest_posts');
})

?>