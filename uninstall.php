<?php

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
	exit();

delete_option('cgs_cache_timeout');

$all_cgs_widgets = get_option('widget_KhCGSocietyLatestPosts');

$transient_key_posts_base = 'cgs-trans-posts-cache-';
$transient_key_userinfo_base = 'cgs-trans-userinfo-cache-';

foreach($all_cgs_widgets as $cgs_widget) {
	delete_transient($transient_key_posts_base . $cgs_widget['userid']);
	delete_transient($transient_key_userinfo_base . $cgs_widget['userid']);
}

?>