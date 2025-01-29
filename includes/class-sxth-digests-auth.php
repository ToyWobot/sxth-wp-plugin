<?php
class SXTH_Digests_Auth
{
   private static $instance = null;
   private $api_base_url = 'http://localhost:8000/api';  # Your API endpoint

   private function __construct()
   {
      // add_action('admin_menu', array($this, 'add_login_page'));
      add_action('admin_init', array($this, 'handle_login'));
      add_action('admin_init', array($this, 'verify_token'));
      add_action('admin_init', array($this, 'check_authentication'));
   }

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function add_login_page()
   {
      add_menu_page(
         'SXTH Digests Login',
         'SXTH Digests',
         'read',
         'sxth-digests-login',
         array($this, 'render_login_page'),
         'dashicons-lock',
         6
      );
   }
   public function render_login_page()
   {
      include_once SXTH_DIGESTS_PLUGIN_DIR . 'admin/partials/login-form.php';
   }

   public function handle_login()
   {
      if (!isset($_POST['sxth_digests_login']))
         return;

      // Verify nonce
      if (!wp_verify_nonce($_POST['_wpnonce'], 'sxth-digests-login')) {
         wp_die('Security check failed');
      }

      $email = sanitize_email($_POST['email']);

      // Step 1: Initiate login with external API
      $response = wp_remote_post($this->api_base_url . '/login', array(
         'body' => json_encode(array('email' => $email)),
         'headers' => array('Content-Type' => 'application/json')
      ));

      if (is_wp_error($response)) {
         wp_die('API connection failed');
      }

      $body = json_decode(wp_remote_retrieve_body($response), true);

      if ($body['status'] == 200) {
         // Store temporary verification data
         set_transient(
            'sxth_digests_temp_' . md5($email),
            $body['verification_id'],
            15 * MINUTE_IN_SECONDS
         );

         // Redirect to token entry
         wp_redirect(admin_url('admin.php?page=sxth-digests-login&step=verify'));
         exit;
      }
   }

   public function verify_token($token)
   {
      if (!isset($_POST['sxth_digests_verify']))
         return;

      // Verify token with external API
      $response = wp_remote_post($this->api_base_url . '/login/verify', array(
         'body' => json_encode(array(
            'email' => "mahadi.dev.pm@gmail.com",
            "code" => $_POST['token'],
            "device_info" => "iPhone",
            "device_id" => "test123",
         )),
         'headers' => array('Content-Type' => 'application/json')
      ));


      if (is_wp_error($response))
         return false;

      $body = json_decode(wp_remote_retrieve_body($response), true);
      if ($body['status'] === 200) {
         // Store valid token in user meta
         update_user_meta(
            get_current_user_id(),
            'sxth_digests_auth_token',
            $body['body']["token"]
         );

         // Store expiration time (example: 1 hour)
         update_user_meta(
            get_current_user_id(),
            'sxth_digests_token_expires',
            time() + 3600
         );


         add_option('sxth_digests_auth_token', $body['body']["token"]);

         // Redirect to main plugin page
         wp_safe_redirect(admin_url('edit.php?post_type=sxth_digest'));
         exit;
         
      }

      return false;
   }

   public function check_authentication()
   {
      // Skip check on login page
      if (isset($_GET['page']) && $_GET['page'] === 'sxth-digests-login')
         return;

      $token = get_user_meta(get_current_user_id(), 'sxth_digests_auth_token', true);
      $expires = get_user_meta(get_current_user_id(), 'sxth_digests_token_expires', true);

      if (!$token || time() > $expires) {
         wp_redirect(admin_url('admin.php?page=sxth-digests-login'));
         exit;
      }
   }

   public static function get_auth_header()
   {
      return array(
         'Authorization' => 'Bearer ' . get_user_meta(
            get_current_user_id(),
            'sxth_digests_auth_token',
            true
         )
      );
   }
}