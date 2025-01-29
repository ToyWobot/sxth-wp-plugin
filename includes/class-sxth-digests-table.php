<?php
class SXTH_Digests_Table
{
   public function render_table()
   {
      // Fetch data from your API
      $data = $this->get_table_data();

      if (empty($data)) {
         echo '<div class="notice notice-error"><p>No data available</p></div>';
         return;
      }
      ?>
      <div class="wrap">
         <h1 class="wp-heading-inline">SXTH Digests</h1>
         <hr class="wp-header-end">

         <table class="wp-list-table widefat fixed striped">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($data as $item): ?>
                  <tr>
                     <td><?php echo esc_html($item['id']); ?></td>
                     <td><?php echo esc_html($item['title']); ?></td>
                     <td><?php echo date_i18n(get_option('date_format'), strtotime($item['date'])); ?></td>
                     <td><?php echo $this->get_status_badge($item['status']); ?></td>
                     <td>
                        <a href="#" class="button view-digest" data-id="<?php echo $item['id']; ?>">View</a>
                     </td>
                  </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
      </div>
      <?php
      $this->enqueue_scripts();
   }

   private function get_table_data()
   {
      $token = get_user_meta(get_current_user_id(), 'sxth_digests_auth_token', true);

      $response = wp_remote_get('https://your-api.com/digests', [
         'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
         ]
      ]);

      if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
         return [];
      }

      return json_decode(wp_remote_retrieve_body($response), true);
   }

   private function get_status_badge($status)
   {
      $statuses = [
         'published' => ['label' => 'Published', 'color' => 'green'],
         'draft' => ['label' => 'Draft', 'color' => 'gray'],
         'pending' => ['label' => 'Pending', 'color' => 'orange']
      ];

      $status = $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'red'];

      return sprintf(
         '<span class="sxth-status-badge" style="background-color: %s; color: white; padding: 2px 8px; border-radius: 3px;">%s</span>',
         esc_attr($status['color']),
         esc_html($status['label'])
      );
   }

   private function enqueue_scripts()
   {
      wp_enqueue_style(
         'sxth-digests-table',
         SXTH_DIGESTS_PLUGIN_URL . 'admin/css/table.css',
         [],
         SXTH_DIGESTS_VERSION
      );

      wp_enqueue_script(
         'sxth-digests-table',
         SXTH_DIGESTS_PLUGIN_URL . 'admin/js/table.js',
         ['jquery'],
         SXTH_DIGESTS_VERSION,
         true
      );

      wp_localize_script('sxth-digests-table', 'sxthDigests', [
         'ajax_url' => admin_url('admin-ajax.php'),
         'nonce' => wp_create_nonce('sxth_digests_nonce')
      ]);
   }

   public function handle_ajax_requests()
   {
      check_ajax_referer('sxth_digests_nonce', 'nonce');

      $action = $_REQUEST['action'] ?? '';
      $digest_id = absint($_POST['digest_id'] ?? 0);

      switch ($action) {
         case 'get_digest_details':
            $this->get_digest_details($digest_id);
            break;
      }

      wp_die();
   }

   private function get_digest_details($digest_id)
   {
      // Implement API call to get details
      $response = [
         'success' => true,
         'data' => [
            'content' => 'Sample content for digest ' . $digest_id
         ]
      ];

      wp_send_json($response);
   }
}