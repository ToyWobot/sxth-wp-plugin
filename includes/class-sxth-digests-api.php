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
         'permission_callback' => function ($request) {
            if ($this->api_permissions_check($request)) {
               return true;
            } else {
               // Get client API key
               $api_key = $request->get_header('X-API-Key');
               // Get Client IP
               $client_ip = $_SERVER['REMOTE_ADDR'];

               return new WP_Error(
                  'rest_forbidden',
                  __("Sorry! Your IP $client_ip or API key $api_key is not allowed."),
                  array('status' => rest_authorization_required_code())
               );
            }
         },
      ));
   }

   public function api_permissions_check($request)
   {
      // Validate API key
      $api_key = $request->get_header('X-API-Key');
      $stored_key = esc_attr(get_option('sxth_digests_api_key'));

      // Get Client IP
      $client_ip = $_SERVER['REMOTE_ADDR'];

      // Get Allowed IPs
      $allowed_ips = explode(', ', get_option('sxth_digests_allowed_ips'));
      $allowed_ips = array_map('trim', $allowed_ips);

      // Validate
      if ($api_key !== $stored_key) {
         return false;
      }

      if (!empty($allowed_ips) && !in_array($client_ip, $allowed_ips)) {
         return false;
      }

      return true;
   }

   public function create_digest_post($request)
   {
      $params = $request->get_params();

      // Validate and sanitize input
      $post_data = array(
         'post_title' => sanitize_text_field($params['title']),
         'post_content' => ($params['content']),
         'post_status' => 'publish',
      );
      $content = $params['content'];
      $pre_content = "";

      return $this->afterFormat($post_data);
   }


   public function afterFormat($post_data)
   {
      // 1. Ensure 'Digests' category exists
      $category_id = $this->get_or_create_digests_category();
      if (is_wp_error($category_id)) {
         return $category_id;
      }

      $post_data["post_category"] = [$category_id];

      $post_id = wp_insert_post($post_data);

      if (is_wp_error($post_id)) {
         return new WP_Error('create_failed', __('Post creation failed', 'sxth-digests'), array('status' => 400));
      }

      return new WP_REST_Response(array(
         'id' => $post_id,
         'message' => __('Digest created successfully'),
         "status" => 200,
      ), 200);
   }

   private function get_or_create_digests_category()
   {
      $category_name = 'Digests';

      // Check if category exists
      $category = term_exists($category_name, 'category');

      if ($category) {
         return $category['term_id'];
      }

      // Create category with admin privileges
      $admin_id = $this->get_admin_user_id();
      $original_user = wp_get_current_user();

      wp_set_current_user($admin_id);
      $category_id = wp_create_category($category_name);
      wp_set_current_user($original_user->ID);

      if (is_wp_error($category_id)) {
         return new WP_Error(
            'category_creation_failed',
            'Could not create Digests category: ' . $category_id->get_error_message()
         );
      }

      return $category_id;
   }



   private function get_admin_user_id()
   {
      $admins = get_users([
         'role' => 'administrator',
         'number' => 1,
         'fields' => 'ID'
      ]);

      return $admins[0] ?? 1; // Fallback to user ID 1
   }
}