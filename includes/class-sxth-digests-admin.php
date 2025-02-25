<?php
class SXTH_Digests_Admin
{
   private static $instance = null;

   private function __construct()
   {
      // $auth = SXTH_Digests_Auth::get_instance();
      // $auth->check_authentication();

      add_action('admin_menu', array($this, 'add_settings_page'));
      add_action('admin_init', array($this, 'register_settings'));
   }

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function add_settings_page()
   {
      add_options_page(
         __('SXTH Settings', 'sxth-settings'),
         __('SXTH Settings', 'sxth-settings'),
         'manage_options',
         'sxth-digests',
         array($this, 'render_settings_page')
      );
   }

   public function register_settings()
   {
      register_setting('sxth_digests_options', 'sxth_digests_api_key');
      register_setting('sxth_digests_options', 'sxth_digests_allowed_ips');
   }

   public function render_settings_page()
   {
      ?>
      <div class="wrap">
         <h1><?php _e('SXTH Digests Settings', 'sxth-digests'); ?></h1>
         <form method="post" action="options.php">
            <?php
            settings_fields('sxth_digests_options');
            do_settings_sections('sxth_digests_options');
            ?>
            <table class="form-table">
               <tr valign="top">
                  <th scope="row"><?php _e('API Key', 'sxth-digests'); ?></th>
                  <td>
                     <input type="text" name="sxth_digests_api_key"
                        value="<?php echo esc_attr(get_option('sxth_digests_api_key')); ?>" class="regular-text" />
                     <p class="description"><?php _e('Use this key for API authentication', 'sxth-digests'); ?></p>
                  </td>
               </tr>
               <tr valign="top">
                  <th scope="row"><?php _e('Allowed IPs', 'sxth-digests'); ?></th>
                  <td>
                     <input type="text" name="sxth_digests_allowed_ips"
                        value="<?php echo esc_attr(get_option('sxth_digests_allowed_ips')); ?>" class="regular-text"
                        placeholder="123.45.67.89, 192.168.1.1" />
                     <p class="description"><?php _e('Comma-separated list of allowed IP addresses', 'sxth-digests'); ?></p>
                  </td>
               </tr>
            </table>
            <?php submit_button(); ?>
         </form>
      </div>
      <?php
   }
}