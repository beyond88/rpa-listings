<?php

namespace RPAListings\Elementor;

if (!defined('ABSPATH')) {
    exit;
}

class DealRoomModal
{
    private static $modal_rendered = false;

    public static function render_modal($project_id)
    {
        if (self::$modal_rendered) {
            return;
        }
        self::$modal_rendered = true;

        $property_name = get_the_title($project_id);
        $addresses = get_post_meta($project_id, 'rpa_project_addresses', true);
        $address = (is_array($addresses) && !empty($addresses[0])) ? $addresses[0] : '';

        // Captcha is now entirely handled by JavaScript to be 100% cache-proof.

?>
        <div id="rpaDealRoomModal" class="rpa-modal">
            <div class="rpa-modal-content">
                <span class="rpa-modal-close" onclick="closeDealRoomModal()">&times;</span>
                <div class="rpa-ca-form-wrap">
                    <h2 class="rpa-ca-title">Confidentiality and Buyer Registration Agreement</h2>
                    <p class="rpa-ca-text">
                        Cushman & Wakefield U.S., Inc. ("Broker") has been retained as the exclusive advisor and broker regarding the sale of the property known as the
                        <strong><?php echo esc_html($property_name); ?></strong><?php if ($address) echo ', ' . esc_html($address); ?>.
                    </p>
                    <div class="rpa-ca-scrollable-text">
                        <p>To receive an Offering Memorandum ("Offering Memorandum") please read, sign and return this completed Confidentiality Agreement to Broker...</p>
                        <p>By accepting the Offering Memorandum, you agree to indemnify, defend, protect and hold Seller and Broker and any affiliate of Seller or Broker harmless...</p>
                        <p>The Seller and Broker each expressly reserve the right, at their sole discretion, to reject any or all expressions of interest...</p>
                        <p>The Offering Memorandum and the contents, except such information which is a matter of public record... are of a confidential nature...</p>
                    </div>

                    <form id="rpaDealRoomForm" class="rpa-ca-form" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($project_id); ?>">
                        <input type="hidden" name="action" value="rpa_submit_deal_form">
                        <?php wp_nonce_field('rpa_deal_form_nonce', 'security'); ?>

                        <div class="rpa-form-fields-container">
                            <div class="rpa-form-group rpa-checkbox-group">
                                <label>
                                    <input type="checkbox" name="agree_terms" required>
                                    I have read and agree to the Confidentiality Agreement <span class="rpa-req">*</span>
                                </label>
                            </div>

                            <div class="rpa-form-row">
                                <div class="rpa-form-group">
                                    <label>First name <span class="rpa-req">*</span></label>
                                    <input type="text" name="first_name" required>
                                </div>
                                <div class="rpa-form-group">
                                    <label>Last name <span class="rpa-req">*</span></label>
                                    <input type="text" name="last_name" required>
                                </div>
                            </div>

                            <div class="rpa-form-group">
                                <label>Company name <span class="rpa-req">*</span></label>
                                <input type="text" name="company_name" required>
                            </div>

                            <div class="rpa-form-group">
                                <label>Email <span class="rpa-req">*</span></label>
                                <input type="email" name="email" required>
                            </div>

                            <div class="rpa-form-group">
                                <label>Phone number <span class="rpa-req">*</span></label>
                                <input type="tel" name="phone_number" required>
                            </div>

                            <div class="rpa-form-group">
                                <label>Signature <span class="rpa-req">*</span></label>
                                <div class="rpa-signature-wrapper">
                                    <canvas id="rpaSignatureCanvas" width="400" height="150"></canvas>
                                    <button type="button" class="rpa-clear-sig" onclick="clearSignature()">Clear</button>
                                </div>
                                <input type="hidden" name="signature_data" id="rpaSignatureData" required>
                            </div>

                            <div class="rpa-form-group rpa-captcha-group">
                                <label>Math Captcha: <span id="rpa-captcha-num1"></span> + <span id="rpa-captcha-num2"></span> = ? <span class="rpa-req">*</span></label>
                                <input type="text" id="rpa-captcha-answer" required placeholder="Enter the result">
                            </div>

                            <div class="rpa-form-actions">
                                <button type="submit" class="rpa-btn rpa-submit-btn">Submit</button>
                            </div>
                        </div>
                        <div class="rpa-form-msg"></div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            // No need for PHP token anymore. Handled in JS.
        </script>
<?php
    }
}
