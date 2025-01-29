<?php
if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
   exit;
}

// Delete options
delete_option('sxth_digests_api_key');

// Remove custom post type posts
$posts = get_posts(array(
   'post_type' => 'sxth_digest',
   'numberposts' => -1,
   'post_status' => 'any'
));

foreach ($posts as $post) {
   wp_delete_post($post->ID, true);
}

// Flush rewrite rules
flush_rewrite_rules();