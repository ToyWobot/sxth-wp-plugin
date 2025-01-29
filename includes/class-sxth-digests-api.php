<?php
class SXTH_Digests_API
{
   private static $instance = null;

   private function __construct()
   {
      add_action('rest_api_init', array($this, 'register_api_routes'));
   }

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function register_api_routes()
   {
      register_rest_route('sxth-digests/v1', '/create', array(
         'methods' => 'POST',
         'callback' => array($this, 'create_digest_post'),
         'permission_callback' => array($this, 'api_permissions_check'),
      ));
   }

   public function api_permissions_check($request)
   {
      // Validate API key
      $api_key = $request->get_header('X-API-Key');
      $stored_key = get_option('sxth_digests_auth_token');

      return $api_key === $stored_key;
   }

   public function create_digest_post($request)
   {
      $params = $request->get_params();

      // Validate and sanitize input
      $post_data = array(
         'post_title' => sanitize_text_field($params['title']),
         'post_content' => array($params['content']),
         'post_type' => 'sxth_digest',
         'post_status' => 'publish',
      );

      $post_id = wp_insert_post($post_data);

      if (is_wp_error($post_id)) {
         return new WP_Error('create_failed', __('Post creation failed', 'sxth-digests'), array('status' => 400));
      }

      // Add meta fields if needed
      if (isset($params['meta'])) {
         foreach ($params['meta'] as $key => $value) {
            update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
         }
      }

      return new WP_REST_Response(array(
         'id' => $post_id,
         'message' => __('Digest created successfully', 'sxth-digests')
      ), 200);
   }
}