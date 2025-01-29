<?php
class SXTH_Digests_Public
{
   private static $instance = null;

   private function __construct()
   {
      add_filter('single_template', array($this, 'custom_digest_template'));
   }

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function custom_digest_template($template)
   {
      global $post;

      if ($post->post_type === 'sxth_digest') {
         // Check theme directory first
         $theme_template = locate_template(array('single-sxth_digest.php'));

         if (!$theme_template) {
            // Use plugin template as fallback
            $plugin_template = SXTH_DIGESTS_PLUGIN_DIR . 'templates/single-sxth_digest.php';
            if (file_exists($plugin_template)) {
               return $plugin_template;
            }
         }
      }

      return $template;
   }
}