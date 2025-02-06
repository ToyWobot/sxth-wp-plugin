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
                  __("Please check your api key or ip. Your ip is $client_ip and key is $api_key"),
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
         'post_content' => json_encode($params['content']),
         'post_status' => 'publish',
      );
      $content = $params['content'];
      $pre_content = "";

      // sections
      $sections_array = $content["sections"];
      $sections_array_count = 0;
      $sections_array_total = count($sections_array);
      foreach ($sections_array as $key => $value) {
         $sections_array_count = $sections_array_count + 1;

         // section
         $sections = $sections_array[$key];
         $sections_count = 0;
         $sections_total = count($sections);
         foreach ($sections as $section_key => $section_value) {
            $sections_count = $sections_count + 1;
            $pre_content .= "<div style='margin-bottom: 5rem'>";
            // summary
            $section = $section_value;
            $summary = $section_value["summary"];
            $pre_content .= "<h2>" . $summary["title"] . "</h2>";
            $summary_content = $summary["content"];
            $summary_content_count = 0;
            foreach ($summary_content as $summary_content_key => $summary_content_value) {
               $summary_content_count = $summary_content_count + 1;

               if ($summary_content_count === 1) {
                  $pre_content .= "<div style='margin-bottom: 4rem'>";
               }

               if ($summary_content_count === 1) {
                  $pre_content .= "<h3 style='font-weight: bold'>Summary</h3><ul style='margin-bottom: 3rem'>";
               }
               $pre_content .= "<li>" . $summary_content[$summary_content_key]["bullet"] . "</li>";

               if ($summary_content_count === count($summary_content)) {
                  $pre_content .= "</ul>";
               }


               if ($summary_content_count === $summary_content_total) {
                  $pre_content .= "</div>";
               }
            }
            // why it's matter
            $why_matters = $content["why_matters"];
            $why_matters_count = 0;
            $why_matters_total = count($why_matters);
            foreach ($why_matters as $why_matters_keys => $why_matters_value) {
               if ($why_matters_value["article_id"] === $section["id"]) {
                  $why_matters_count = $why_matters_count + 1;

                  if ($why_matters_count === 1) {
                     $pre_content .= "<div style='margin-bottom: 4rem'>";
                  }

                  $why_matter = $why_matters_value["why_matter"];
                  $pre_content .= "<h3 style='font-weight: bold'>Why it matters? | Strategic Impact</h3>";
                  $pre_content .= "<p>" . $why_matter["detailed"]["introduction"] . "</p>";

                  // focus key
                  $focus_keys = $why_matter["detailed"]["keyAreasOfFocus"];
                  $focus_keys_count = 0;
                  $focus_keys_total = count($focus_keys);
                  foreach ($focus_keys as $focus_key => $focus_value) {
                     $focus_keys_count = $focus_keys_count + 1;
                     if ($focus_keys_count === 1) {
                        $pre_content .= "<h3 style='font-weight: bold'>Key Areas Of Focus</h3>";
                        $pre_content .= "<ul>";
                     }

                     $pre_content .= "<li>" . $focus_value["focusArea"] . "</li>";

                     if ($focus_keys_total === $focus_keys_count) {
                        $pre_content .= "</ul>";
                     }
                  }

                  if ($why_matters_count === $why_matters_total) {
                     $pre_content .= "</div>";
                  }
               }
            }
            $pre_content .= "</div>";
         }

         if ($sections_array_total === $sections_array_count) {
            $post_data["post_content"] = $pre_content;
            return $this->afterFormat($post_data);
         }
      }
   }


   public function afterFormat($post_data)
   {
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
}