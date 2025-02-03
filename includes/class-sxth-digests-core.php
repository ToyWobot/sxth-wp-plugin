<?php
class SXTH_Digests_Core
{
   private static $instance = null;

   private function __construct()
   {
      // Register custom post type
      add_action('init', array($this, 'register_digest_post_type'));
   }

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function register_digest_post_type()
   {
      // $labels = array(
      //    'name' => __('Digests', 'sxth-digests'),
      //    'singular_name' => __('Digest', 'sxth-digests'),
      // );

      // $args = array(
      //    'labels' => $labels,
      //    'public' => true,
      //    'has_archive' => true,
      //    'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
      //    'show_in_rest' => true,
      //    'menu_icon' => 'dashicons-media-document',
      // );

      // register_post_type('sxth_digest', $args);
   }

   public static function activate()
   {
      // Activation code here
      flush_rewrite_rules();
   }

   public static function deactivate()
   {
      // Deactivation code here
      flush_rewrite_rules();
   }
}