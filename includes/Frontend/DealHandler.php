<?php

namespace RPAListings\Frontend;

use Dompdf\Dompdf;
use Dompdf\Options;

if (!defined('ABSPATH')) {
    exit;
}

class DealHandler
{
    public function register()
    {
        add_action('wp_ajax_rpa_submit_deal_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_rpa_submit_deal_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_rpa_download_deal_docs', [$this, 'handle_download_docs']);
        add_action('wp_ajax_nopriv_rpa_download_deal_docs', [$this, 'handle_download_docs']);
        add_action('wp_ajax_rpa_resend_magic_link', [$this, 'handle_resend_magic_link']);
        add_action('template_redirect', [$this, 'handle_magic_link']);
        add_action('template_redirect', [$this, 'prevent_caching_if_authenticated']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('before_delete_post', [$this, 'clear_deal_access_transient']);
    }

    public function clear_deal_access_transient($post_id)
    {
        if (get_post_type($post_id) !== 'deal_entry') {
            return;
        }

        $token = get_post_meta($post_id, 'rpa_magic_token', true);
        if ($token) {
            delete_transient('rpa_magic_token_' . md5($token));
        }
    }

    public function enqueue_assets()
    {
        if (!is_singular('project')) {
            return;
        }

        wp_enqueue_style('rpa-deal-room', RPA_LISTINGS_URL . 'assets/css/deal-room.css', [], RPA_LISTINGS_VERSION);
        wp_enqueue_script('signature-pad', 'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js', [], '4.1.7', true);
        wp_enqueue_script('rpa-deal-room', RPA_LISTINGS_URL . 'assets/js/deal-room.js', ['jquery', 'signature-pad'], RPA_LISTINGS_VERSION, true);

        wp_localize_script('rpa-deal-room', 'rpaDealRoom', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rpa_deal_form_nonce')
        ]);
    }

    public function handle_resend_magic_link()
    {
        check_ajax_referer('rpa_listings_admin', 'security');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $entry_id = intval($_POST['entry_id'] ?? 0);
        if (!$entry_id) {
            wp_send_json_error(['message' => 'Invalid entry ID.']);
        }

        $project_id = get_post_meta($entry_id, 'rpa_project_id', true);
        $first_name = get_post_meta($entry_id, 'rpa_first_name', true);
        $last_name = get_post_meta($entry_id, 'rpa_last_name', true);
        $company_name = get_post_meta($entry_id, 'rpa_company_name', true);
        $email = get_post_meta($entry_id, 'rpa_email', true);
        $signature_data = get_post_meta($entry_id, 'rpa_signature_data', true);
        $magic_token = get_post_meta($entry_id, 'rpa_magic_token', true);

        if (!$project_id || !$email || !$magic_token) {
            wp_send_json_error(['message' => 'Missing data to send email.']);
        }

        $pdf_path = $this->generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data);
        $this->send_email_with_magic_link($email, $project_id, $magic_token, $pdf_path);

        wp_send_json_success(['message' => 'Email sent successfully!']);
    }

    public function handle_form_submission()
    {
        check_ajax_referer('rpa_deal_form_nonce', 'security');

        $project_id = intval($_POST['project_id'] ?? 0);
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $company_name = sanitize_text_field($_POST['company_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone_number'] ?? '');
        $signature_data = $_POST['signature_data'] ?? ''; // Base64 image data

        // Captcha is now validated purely on the frontend via JS.

        if (!$project_id || !$first_name || !$last_name || !$email || !$signature_data) {
            wp_send_json_error(['message' => 'Missing required fields.']);
        }

        // Generate magic token
        $magic_token = wp_generate_password(32, false);

        // Create deal entry post
        $post_title = $first_name . ' ' . $last_name . ' - ' . get_the_title($project_id);
        $entry_id = wp_insert_post([
            'post_title' => $post_title,
            'post_type' => 'deal_entry',
            'post_status' => 'publish'
        ]);

        if (is_wp_error($entry_id)) {
            wp_send_json_error(['message' => 'Failed to save entry.']);
        }

        // Save meta
        update_post_meta($entry_id, 'rpa_project_id', $project_id);
        update_post_meta($entry_id, 'rpa_first_name', $first_name);
        update_post_meta($entry_id, 'rpa_last_name', $last_name);
        update_post_meta($entry_id, 'rpa_company_name', $company_name);
        update_post_meta($entry_id, 'rpa_email', $email);
        update_post_meta($entry_id, 'rpa_phone_number', $phone);
        update_post_meta($entry_id, 'rpa_signature_data', $signature_data);
        update_post_meta($entry_id, 'rpa_magic_token', $magic_token);
        update_post_meta($entry_id, 'rpa_signed_date', current_time('mysql'));

        // Generate PDF
        $pdf_path = $this->generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data);

        // Send Email
        $this->send_email_with_magic_link($email, $project_id, $magic_token, $pdf_path);

        // Set cookie directly in response if possible, but AJAX can't easily set reliable frontend cookies sometimes.
        // It's better to pass token back and let JS set cookie, or set cookie header.
        setcookie('rpa_deal_access_' . $project_id, $magic_token, time() + (86400 * 365), '/');

        wp_send_json_success([
            'message' => 'Agreement signed successfully! Check your email.',
            'token' => $magic_token
        ]);
    }

    private function generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data)
    {
        $property_name = get_the_title($project_id);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = '
        <html>
        <head><style>body { font-family: sans-serif; font-size: 14px; }</style></head>
        <body>
            <h2>Confidentiality and Buyer Registration Agreement</h2>
            <p><strong>Property:</strong> ' . esc_html($property_name) . '</p>
            <p>I have read and agree to the Confidentiality Agreement.</p>
            <br><br>
            <p><strong>Name:</strong> ' . esc_html($first_name . ' ' . $last_name) . '</p>
            <p><strong>Company:</strong> ' . esc_html($company_name) . '</p>
            <p><strong>Date:</strong> ' . current_time('d-m-Y H:i:s') . '</p>
            <br>
            <p><strong>Signature:</strong></p>
            <img src="' . esc_attr($signature_data) . '" width="200" style="border-bottom: 1px solid #000;" />
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/rpa_deals';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_filename = 'CA_Agreement_' . $entry_id . '.pdf';
        $pdf_path = $pdf_dir . '/' . $pdf_filename;
        $pdf_content = $dompdf->output();
        
        if (empty($pdf_content)) {
            return false;
        }

        if (file_put_contents($pdf_path, $pdf_content) === false) {
            return false;
        }

        return $pdf_path;
    }

    private function send_email_with_magic_link($email, $project_id, $magic_token, $pdf_path)
    {
        $project_url  = get_permalink($project_id);
        $magic_link   = add_query_arg(['deal_token' => $magic_token], $project_url);
        $property_name = get_the_title($project_id);
        $site_name    = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $admin_email  = get_option('admin_email');
        $current_year = gmdate('Y');
        $signed_date  = current_time('F j, Y');

        $subject = 'Deal Room Access Granted – ' . $property_name;

        $message = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . esc_html($subject) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f0;font-family:\'Georgia\',\'Times New Roman\',serif;">

  <!-- Preheader -->
  <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
    Your Confidentiality Agreement is signed. Access your Deal Room documents now.
  </div>

  <!-- Wrapper -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f4f0;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        <!-- Card -->
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- Header / Hero -->
          <tr>
            <td style="background-color:#1a1a1a;padding:40px 48px 36px;text-align:center;">
              <p style="margin:0 0 6px 0;font-family:\'Georgia\',serif;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#C9A96E;">Recreational Property Advisors</p>
              <h1 style="margin:0;font-family:\'Georgia\',serif;font-size:22px;font-weight:400;color:#ffffff;letter-spacing:1px;line-height:1.4;">Deal Room Access Confirmed</h1>
              <div style="width:40px;height:2px;background-color:#C9A96E;margin:18px auto 0;"></div>
            </td>
          </tr>

          <!-- Gold accent bar -->
          <tr>
            <td style="background-color:#C9A96E;height:4px;font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:44px 48px 36px;">

              <p style="margin:0 0 22px 0;font-family:\'Arial\',sans-serif;font-size:15px;color:#333333;line-height:1.7;">
                Thank you for completing your Confidentiality Agreement. Your access to the deal room has been granted.
              </p>

              <!-- Property info box -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f9f7f3;border-left:3px solid #C9A96E;border-radius:2px;margin-bottom:32px;">
                <tr>
                  <td style="padding:20px 24px;">
                    <p style="margin:0 0 4px 0;font-family:\'Arial\',sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#999999;">Property</p>
                    <p style="margin:0 0 16px 0;font-family:\'Georgia\',serif;font-size:17px;color:#1a1a1a;font-weight:400;">' . esc_html($property_name) . '</p>
                    <p style="margin:0 0 4px 0;font-family:\'Arial\',sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#999999;">Agreement Signed</p>
                    <p style="margin:0;font-family:\'Arial\',sans-serif;font-size:14px;color:#444444;">' . esc_html($signed_date) . '</p>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 10px 0;font-family:\'Arial\',sans-serif;font-size:14px;color:#555555;line-height:1.7;">
                Use your private access link below to view all deal room documents at any time. <strong style="color:#1a1a1a;">Do not share this link</strong> — it is unique to you.
              </p>

              <!-- CTA Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:32px auto;">
                <tr>
                  <td style="border-radius:3px;background-color:#1a1a1a;">
                    <a href="' . esc_url($magic_link) . '" target="_blank"
                       style="display:inline-block;padding:16px 40px;font-family:\'Arial\',sans-serif;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#C9A96E;text-decoration:none;font-weight:600;">
                      Access Deal Room &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Attachment note -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #eeeeee;margin-top:12px;padding-top:28px;">
                <tr>
                  <td width="36" valign="top" style="padding-top:2px;">
                    <div style="width:32px;height:32px;background-color:#f4f4f0;border-radius:50%;text-align:center;line-height:32px;">
                      <span style="font-size:16px;">📎</span>
                    </div>
                  </td>
                  <td style="padding-left:12px;">
                    <p style="margin:0 0 4px 0;font-family:\'Arial\',sans-serif;font-size:13px;font-weight:600;color:#1a1a1a;">Signed Agreement Attached</p>
                    <p style="margin:0;font-family:\'Arial\',sans-serif;font-size:13px;color:#777777;line-height:1.6;">A PDF copy of your signed Confidentiality Agreement is attached to this email for your records.</p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 48px;">
              <div style="border-top:1px solid #eeeeee;"></div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:28px 48px 36px;text-align:center;">
              <p style="margin:0 0 8px 0;font-family:\'Arial\',sans-serif;font-size:12px;color:#aaaaaa;line-height:1.7;">
                This email was sent by <strong style="color:#888888;">' . esc_html($site_name) . '</strong> because you signed a Confidentiality Agreement.<br>
                If you did not request this, please contact us at <a href="mailto:' . esc_attr($admin_email) . '" style="color:#C9A96E;text-decoration:none;">' . esc_html($admin_email) . '</a>.
              </p>
              <p style="margin:16px 0 0 0;font-family:\'Arial\',sans-serif;font-size:11px;color:#cccccc;">&copy; ' . $current_year . ' ' . esc_html($site_name) . '. All rights reserved.</p>
            </td>
          </tr>

        </table>
        <!-- /Card -->

      </td>
    </tr>
  </table>

</body>
</html>';

        $headers = [
            'Reply-To: ' . $admin_email,
        ];

        // Clean site name for headers to avoid breaking the From field
        $clean_site_name = str_replace(['"', "'", '<', '>'], '', $site_name);

        // Use filters for From address and name with high priority
        $from_name_filter = function() use ($clean_site_name) { return $clean_site_name; };
        $from_email_filter = function() use ($admin_email) { return $admin_email; };

        add_filter('wp_mail_from_name', $from_name_filter, 999);
        add_filter('wp_mail_from', $from_email_filter, 999);
        add_filter('wp_mail_content_type', function() { return 'text/html'; }, 999);

        $attachments = ($pdf_path && file_exists($pdf_path)) ? [$pdf_path] : [];

        wp_mail($email, $subject, $message, $headers, $attachments);

        remove_filter('wp_mail_from_name', $from_name_filter, 999);
        remove_filter('wp_mail_from', $from_email_filter, 999);
        remove_filter('wp_mail_content_type', function() { return 'text/html'; }, 999);
    }

    public function handle_magic_link()
    {
        $has_token = isset($_GET['deal_token']);
        $has_refresh = isset($_GET['rpa_refresh']);

        if ($has_token) {
            $token = sanitize_text_field($_GET['deal_token']);

            // Validate token
            $transient_key = 'rpa_magic_token_' . md5($token);
            $project_id = get_transient($transient_key);

            if (false === $project_id) {
                $args = [
                    'post_type' => 'deal_entry',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => [
                        [
                            'key' => 'rpa_magic_token',
                            'value' => $token,
                            'compare' => '='
                        ]
                    ]
                ];

                $query = new \WP_Query($args);
                if (!empty($query->posts)) {
                    $project_id = get_post_meta($query->posts[0], 'rpa_project_id', true);
                    if ($project_id) {
                        set_transient($transient_key, $project_id, 30 * DAY_IN_SECONDS);
                    }
                }
            }

            if ($project_id) {
                if (!defined('DONOTCACHEPAGE')) {
                    define('DONOTCACHEPAGE', true);
                }
                nocache_headers();

                $cookie_name = 'rpa_deal_access_' . $project_id;
                setcookie($cookie_name, $token, time() + (86400 * 365), '/');
                $_COOKIE[$cookie_name] = $token; // Make available to current request immediately
            }
        }

        if ($has_token || $has_refresh) {
            $project_id = get_the_ID();
            if ($project_id) {
                $clean_url = get_permalink($project_id);
                add_action('wp_footer', function () use ($clean_url) {
                    echo '<script>if(window.history&&history.replaceState){history.replaceState(null,"","' . esc_js($clean_url) . '");}</script>';
                });
            }
        }
    }

    public function handle_download_docs()
    {
        check_ajax_referer('rpa_deal_form_nonce', 'security');

        $project_id = intval($_POST['project_id'] ?? 0);
        $file_ids = isset($_POST['file_ids']) ? array_map('sanitize_text_field', (array)$_POST['file_ids']) : [];

        if (!$project_id || empty($file_ids)) {
            wp_send_json_error(['message' => 'No files selected.']);
        }

        // Validate access
        if (!self::has_access($project_id)) {
            wp_send_json_error(['message' => 'Access denied. Invalid session.']);
        }

        // Create ZIP
        $upload_dir = wp_upload_dir();
        $zip_dir = $upload_dir['basedir'] . '/rpa_deals/zips';
        if (!file_exists($zip_dir)) {
            wp_mkdir_p($zip_dir);
        }

        $zip_filename = 'Deal_Documents_' . $project_id . '_' . time() . '.zip';
        $zip_path = $zip_dir . '/' . $zip_filename;

        $zip = new \ZipArchive();
        if ($zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            wp_send_json_error(['message' => 'Failed to create ZIP archive.']);
        }

        $documents_json = get_post_meta($project_id, 'rpa_project_documents', true);
        $docs = json_decode($documents_json, true);
        if (!is_array($docs)) {
            $docs = [];
        }

        $added_urls = [];
        $this->add_files_to_zip($zip, $docs, $file_ids, '', $added_urls);

        if ($zip->numFiles == 0) {
            $zip->close();
            unlink($zip_path);
            wp_send_json_error(['message' => 'No files found to download.']);
        }

        if ($zip->numFiles == 1 && count($added_urls) === 1) {
            $zip->close();
            unlink($zip_path);
            wp_send_json_success(['zip_url' => $added_urls[0]]);
        }

        $zip->close();

        $zip_url = $upload_dir['baseurl'] . '/rpa_deals/zips/' . $zip_filename;

        wp_send_json_success(['zip_url' => $zip_url]);
    }

    private function add_files_to_zip($zip, $items, $file_ids, $current_path = '', &$added_urls = [])
    {
        foreach ($items as $item) {
            if ($item['type'] === 'folder') {
                $new_path = $current_path . $item['name'] . '/';
                // If folder is selected, pass 'ALL' to include everything inside it
                $is_selected = ($file_ids === 'ALL' || (is_array($file_ids) && in_array($item['id'], $file_ids)));
                $this->add_files_to_zip($zip, $item['children'] ?? [], $is_selected ? 'ALL' : $file_ids, $new_path, $added_urls);
            } elseif ($file_ids === 'ALL' || (is_array($file_ids) && in_array($item['id'], $file_ids))) {
                $file_url = $item['url'];
                // Convert URL to absolute local path to add to zip
                $upload_dir = wp_upload_dir();
                $base_url = $upload_dir['baseurl'];
                $base_dir = $upload_dir['basedir'];

                if (strpos($file_url, $base_url) === 0) {
                    $local_path = str_replace($base_url, $base_dir, $file_url);
                    if (file_exists($local_path)) {
                        $zip->addFile($local_path, ltrim($current_path . $item['name'], '/'));
                        $added_urls[] = $file_url;
                    }
                }
            }
        }
    }

    public function prevent_caching_if_authenticated()
    {
        if (!is_singular('project')) {
            return;
        }

        $project_id = get_the_ID();
        if (self::has_access($project_id)) {
            if (!defined('DONOTCACHEPAGE')) {
                define('DONOTCACHEPAGE', true);
            }
            nocache_headers();
        }
    }

    public static function has_access($project_id)
    {
        $cookie_name = 'rpa_deal_access_' . $project_id;
        if (!isset($_COOKIE[$cookie_name])) {
            return false;
        }

        $token = sanitize_text_field($_COOKIE[$cookie_name]);

        $transient_key = 'rpa_magic_token_' . md5($token);
        $cached_project_id = get_transient($transient_key);

        if (false === $cached_project_id) {
            $args = [
                'post_type' => 'deal_entry',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'rpa_project_id',
                        'value' => $project_id,
                        'compare' => '='
                    ],
                    [
                        'key' => 'rpa_magic_token',
                        'value' => $token,
                        'compare' => '='
                    ]
                ]
            ];

            $query = new \WP_Query($args);
            if (empty($query->posts)) {
                return false;
            }
            set_transient($transient_key, $project_id, 30 * DAY_IN_SECONDS);
            return true;
        }

        return (string)$cached_project_id === (string)$project_id;
    }
}
