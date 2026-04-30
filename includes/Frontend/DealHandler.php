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
        $signed_date = get_post_meta($entry_id, 'rpa_user_signed_date', true);

        if (!$project_id || !$email || !$magic_token) {
            wp_send_json_error(['message' => 'Missing data to send email.']);
        }

        $pdf_path = $this->generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data, $signed_date);
        $this->send_email_with_magic_link($email, $project_id, $magic_token, $pdf_path);

        wp_send_json_success(['message' => 'Email sent successfully!']);
    }

    public function handle_form_submission()
    {
        check_ajax_referer('rpa_deal_form_nonce', 'security');

        $project_id = intval($_POST['project_id'] ?? 0);
        $full_name = sanitize_text_field($_POST['name'] ?? '');
        $name_parts = explode(' ', trim($full_name), 2);
        $first_name = $name_parts[0] ?? '';
        $last_name = $name_parts[1] ?? '';
        $company_name = sanitize_text_field($_POST['company_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = ''; // Removed from frontend but keep variable for compatibility
        $signature_data = $_POST['signature_data'] ?? ''; // Base64 image data
        $signed_date = sanitize_text_field($_POST['signed_date'] ?? current_time('Y-m-d'));

        // Captcha is now validated purely on the frontend via JS.

        if (!$project_id || !$full_name || !$email || !$signature_data) {
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
        update_post_meta($entry_id, 'rpa_user_signed_date', $signed_date);

        // Generate PDF
        $pdf_path = $this->generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data, $signed_date);

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

    private function generate_pdf($entry_id, $project_id, $first_name, $last_name, $company_name, $signature_data, $signed_date = null)
    {
        $property_name = get_the_title($project_id);
        $addresses = get_post_meta($project_id, 'rpa_project_addresses', true);
        $address = (is_array($addresses) && !empty($addresses[0])) ? $addresses[0] : '';
        $full_name = trim($first_name . ' ' . $last_name);
        if (!$signed_date) {
            $signed_date = current_time('m/d/Y');
        } else {
            $time = strtotime($signed_date);
            if ($time) {
                $signed_date = date('m/d/Y', $time);
            }
        }

        // Logo (use PNG version - DomPDF doesn't support webp)
        $logo_path = RPA_LISTINGS_DIR . 'assets/images/cw-rpa-logo.png';
        $logo_data = '';
        if (file_exists($logo_path)) {
            $logo_data = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = '
        <html>
        <head>
            <style>
                @page { margin: 40px 50px; }
                body { font-family: "Times New Roman", Times, serif; font-size: 11px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
                .header { text-align: right; margin-bottom: 10px; }
                .header img { height: 55px; }
                h1 { text-align: center; font-size: 14px; font-weight: bold; margin: 0 0 18px 0; }
                p { margin: 0 0 10px 0; text-align: justify; }
                .fields { margin-top: 25px; }
                .agree-text { margin-bottom: 18px; }
                .field-row { margin-bottom: 12px; position: relative; }
                .field-label { font-size: 11px; }
                .field-line { display: inline-block; border-bottom: 1px solid #000; width: 300px; min-height: 14px; vertical-align: bottom; padding-left: 4px; font-size: 11px; }
                .sig-img { max-height: 40px; max-width: 250px; vertical-align: bottom; }
            </style>
        </head>
        <body>
            ' . ($logo_data ? '<div class="header"><img src="' . $logo_data . '" /></div>' : '') . '

            <h1>Confidentiality and Buyer Registration Agreement</h1>

            <p>Confidentiality and Buyer Registration Agreement Cushman &amp; Wakefield U.S., Inc. ("Broker") has been retained as the exclusive advisor and broker regarding the off-market sale of the property known as <strong>' . esc_html($property_name) . '</strong>' . ($address ? ' located at <strong>' . esc_html($address) . '</strong>' : '') . '. To receive an Offering Memorandum and or financials please read, sign, and return this completed Confidentiality Agreement to Broker. The Offering Memorandum has been prepared by Broker for use by a limited number of parties and does not purport to provide a necessarily accurate summary of the property or any of the documents related thereto, nor does it purport to be all-inclusive or to contain all the information which prospective Buyers may need or desire.</p>

            <p>All projections have been developed by Broker and designated sources and are based upon assumptions relating to the general economy, competition, and other factors beyond the control of the Seller and therefore are subject to variation. No representation is made by Broker or the Seller as to the accuracy or completeness of the information contained herein, and nothing contained herein shall be relied on as a promise or representation as to the future performance of the property. Although the information contained herein is believed to be correct, the Seller and its employees disclaim any responsibility for inaccuracies and expect prospective purchasers to exercise independent due diligence in verifying all such information. Further, Broker, the Seller and its employees disclaim all liability for representations and warranties, expressed and implied, contained in or omitted from the Offering Memorandum or any other written or oral communication transmitted or made available to the Buyer.</p>

            <p>The Offering Memorandum does not constitute a representation that there has been no change in the business or affairs of the property or the Owner since the date of preparation of the Offering Memorandum. Analysis and verification of the information contained in the Offering Memorandum are solely the responsibility of the prospective Buyer. Additional information and an opportunity to inspect the property will be made available upon written request to interested and qualified prospective Buyers. By accepting the Offering Memorandum, you agree to indemnify, defend, protect, and hold Seller and Broker and any affiliate of Seller or Broker harmless from and against any and all claims, damages, demands, liabilities, losses, costs or expenses (including reasonable attorney\'s fees, collectively "Claims") arising, directly or indirectly from any actions or omissions of Buyer, its employees, officers, directors or agents.</p>

            <p>By accepting the Offering Memorandum, Buyer acknowledges that it is aware that any Agent/Broker other than Cushman &amp; Wakefield, must be compensated by Buyer as Cushman &amp; Wakefield is not cooperating on fees. Furthermore, Buyer acknowledges that it has not had any discussion regarding this Property\'s Sale with any other broker or agent other than Broker or an agent/broker properly identified through this registration process, including but not limited to, resolutions of incomplete, conflicting, or duplicate registrations. Buyer shall indemnify and hold Seller and Broker harmless from and against any claims, causes of action or liabilities, including, without limitation, reasonable attorney\'s fees and court costs which may be incurred with respect to any claims for other real estate commissions, broker\'s fees, or finder\'s fees in relation to or in connection with the Property to the extent claimed, through or under Seller.</p>

            <p>The Seller and Broker each expressly reserve the right, at their sole discretion, to reject any or all expressions of interest or offers regarding the Property and/or to terminate discussions with any entity at any time with or without notice. The Seller shall have no legal commitment or obligations to any entity reviewing the Offering Memorandum or making an offer to purchase the Property unless a written agreement for the purchase of the Property has been fully executed, delivered, and approved by the Seller and its legal counsel, and any conditions to the Seller\'s obligation thereunder have been satisfied or waived. The Offering Memorandum and the contents, except such information which is a matter of public record or is provided in sources available to the public, are of a confidential nature. By accepting the Offering Memorandum, you agree that you will hold and treat it in the strictest confidence, that you will not photocopy or duplicate it, that you will not disclose the Offering Memorandum, financials or any of the contents to any other entity (except outside advisors retained by you, if necessary, for your determination of whether or not to make an offer and from whom you have obtained an agreement of confidentiality)without prior written authorization of the Seller or Broker, and that you will not use the Offering Memorandum or any of the contents in any fashion or manner detrimental to the interest of the Seller or Broker. No employee of seller or at the subject property is to be contacted without the written approval of the listing agents and doing so would be a violation of this confidentiality agreement.</p>

            <div class="fields">
                <p class="agree-text"><span style="display:inline-block; width:12px; height:12px; border:1px solid #000; margin-right:5px; text-align:center; line-height:12px; font-size:10px;">X</span> I have read &amp; agree to terms of the Confidentiality Agreement above.</p>

                <div class="field-row">
                    <span class="field-label">Name:</span> <span class="field-line">' . esc_html($full_name) . '</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Company:</span> <span class="field-line">' . esc_html($company_name) . '</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Signature:</span> <span class="field-line"><img class="sig-img" src="' . esc_attr($signature_data) . '" /></span>
                </div>
                <div class="field-row">
                    <span class="field-label">Date:</span> <span class="field-line">' . esc_html($signed_date) . '</span>
                </div>
            </div>
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
