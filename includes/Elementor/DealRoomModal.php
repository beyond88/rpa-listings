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

        // Logo URL from plugin assets
        $logo_url = RPA_LISTINGS_URL . 'assets/images/CW %26 RPA Logo - New.webp';

?>
        <div id="rpaDealRoomModal" class="rpa-modal">
            <div class="rpa-modal-content rpa-ca-document">
                <span class="rpa-modal-close" onclick="closeDealRoomModal()">&times;</span>

                <div class="rpa-ca-form-wrap">
                    <!-- Logo Header -->
                    <div class="rpa-ca-logo-header">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="Recreational Property Advisors" class="rpa-ca-logo">
                    </div>

                    <!-- Document Title -->
                    <h2 class="rpa-ca-doc-title">Confidentiality and Buyer Registration Agreement</h2>

                    <!-- Full Document Body -->
                    <div class="rpa-ca-doc-body">
                        <p>Confidentiality and Buyer Registration Agreement Cushman & Wakefield U.S., Inc. ("Broker") has been retained as the exclusive advisor and broker regarding the off-market sale of the property known as <strong><?php echo esc_html($property_name); ?></strong><?php if ($address) echo ' located at <strong>' . esc_html($address) . '</strong>'; ?>. To receive an Offering Memorandum and or financials please read, sign, and return this completed Confidentiality Agreement to Broker. The Offering Memorandum has been prepared by Broker for use by a limited number of parties and does not purport to provide a necessarily accurate summary of the property or any of the documents related thereto, nor does it purport to be all-inclusive or to contain all the information which prospective Buyers may need or desire.</p>

                        <p>All projections have been developed by Broker and designated sources and are based upon assumptions relating to the general economy, competition, and other factors beyond the control of the Seller and therefore are subject to variation. No representation is made by Broker or the Seller as to the accuracy or completeness of the information contained herein, and nothing contained herein shall be relied on as a promise or representation as to the future performance of the property. Although the information contained herein is believed to be correct, the Seller and its employees disclaim any responsibility for inaccuracies and expect prospective purchasers to exercise independent due diligence in verifying all such information. Further, Broker, the Seller and its employees disclaim all liability for representations and warranties, expressed and implied, contained in or omitted from the Offering Memorandum or any other written or oral communication transmitted or made available to the Buyer.</p>

                        <p>The Offering Memorandum does not constitute a representation that there has been no change in the business or affairs of the property or the Owner since the date of preparation of the Offering Memorandum. Analysis and verification of the information contained in the Offering Memorandum are solely the responsibility of the prospective Buyer. Additional information and an opportunity to inspect the property will be made available upon written request to interested and qualified prospective Buyers. By accepting the Offering Memorandum, you agree to indemnify, defend, protect, and hold Seller and Broker and any affiliate of Seller or Broker harmless from and against any and all claims, damages, demands, liabilities, losses, costs or expenses (including reasonable attorney's fees, collectively "Claims") arising, directly or indirectly from any actions or omissions of Buyer, its employees, officers, directors or agents.</p>

                        <p>By accepting the Offering Memorandum, Buyer acknowledges that it is aware that any Agent/Broker other than Cushman & Wakefield, must be compensated by Buyer as Cushman & Wakefield is not cooperating on fees. Furthermore, Buyer acknowledges that it has not had any discussion regarding this Property's Sale with any other broker or agent other than Broker or an agent/broker properly identified through this registration process, including but not limited to, resolutions of incomplete, conflicting, or duplicate registrations. Buyer shall indemnify and hold Seller and Broker harmless from and against any claims, causes of action or liabilities, including, without limitation, reasonable attorney's fees and court costs which may be incurred with respect to any claims for other real estate commissions, broker's fees, or finder's fees in relation to or in connection with the Property to the extent claimed, through or under Seller.</p>

                        <p>The Seller and Broker each expressly reserve the right, at their sole discretion, to reject any or all expressions of interest or offers regarding the Property and/or to terminate discussions with any entity at any time with or without notice. The Seller shall have no legal commitment or obligations to any entity reviewing the Offering Memorandum or making an offer to purchase the Property unless a written agreement for the purchase of the Property has been fully executed, delivered, and approved by the Seller and its legal counsel, and any conditions to the Seller's obligation thereunder have been satisfied or waived. The Offering Memorandum and the contents, except such information which is a matter of public record or is provided in sources available to the public, are of a confidential nature. By accepting the Offering Memorandum, you agree that you will hold and treat it in the strictest confidence, that you will not photocopy or duplicate it, that you will not disclose the Offering Memorandum, financials or any of the contents to any other entity (except outside advisors retained by you, if necessary, for your determination of whether or not to make an offer and from whom you have obtained an agreement of confidentiality)without prior written authorization of the Seller or Broker, and that you will not use the Offering Memorandum or any of the contents in any fashion or manner detrimental to the interest of the Seller or Broker. No employee of seller or at the subject property is to be contacted without the written approval of the listing agents and doing so would be a violation of this confidentiality agreement.</p>
                    </div>

                    <!-- Form Section -->
                    <form id="rpaDealRoomForm" class="rpa-ca-form" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($project_id); ?>">
                        <input type="hidden" name="action" value="rpa_submit_deal_form">
                        <?php wp_nonce_field('rpa_deal_form_nonce', 'security'); ?>

                        <div class="rpa-form-fields-container rpa-ca-doc-fields">
                            <label class="rpa-ca-agree-text">
                                <input type="checkbox" name="agree_terms" required> I have read and agree to the Confidentiality Agreement <span class="rpa-req">*</span>
                            </label>

                            <div class="rpa-ca-field-row">
                                <label>Name:</label>
                                <input type="text" name="name" required class="rpa-ca-field-input">
                            </div>

                            <div class="rpa-ca-field-row">
                                <label>Company:</label>
                                <input type="text" name="company_name" required class="rpa-ca-field-input">
                            </div>

                            <div class="rpa-ca-field-row">
                                <label>Email:</label>
                                <input type="email" name="email" required class="rpa-ca-field-input">
                            </div>

                            <div class="rpa-ca-field-row rpa-ca-signature-row">
                                <label>Signature:</label>
                                <div class="rpa-ca-sig-wrap">
                                    <canvas id="rpaSignatureCanvas" width="400" height="60"></canvas>
                                    <button type="button" class="rpa-clear-sig" onclick="clearSignature()">Clear</button>
                                </div>
                                <input type="hidden" name="signature_data" id="rpaSignatureData" required>
                            </div>

                            <div class="rpa-ca-field-row">
                                <label>Date:</label>
                                <input type="date" name="signed_date" required class="rpa-ca-field-input rpa-ca-date-input">
                            </div>

                            <div class="rpa-ca-captcha-row">
                                <label>Math Captcha: <span id="rpa-captcha-num1"></span> + <span id="rpa-captcha-num2"></span> = ? <span class="rpa-req">*</span></label>
                                <input type="text" id="rpa-captcha-answer" required placeholder="Enter the result" class="rpa-ca-field-input rpa-ca-captcha-input">
                            </div>

                            <div class="rpa-ca-submit-row">
                                <button type="submit" class="rpa-btn rpa-submit-btn">SUBMIT</button>
                            </div>
                        </div>
                        <div class="rpa-form-msg"></div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }
}
