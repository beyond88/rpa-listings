<?php
require_once('../../../wp-load.php');

$_POST = [
    'action' => 'rpa_submit_deal_form',
    'project_id' => 123, // dummy
    'name' => 'John Doe',
    'company_name' => 'Test Co',
    'email' => 'test@example.com',
    'phone' => '1234567890',
    'signature_data' => 'testsig',
    'signature_type' => 'type',
    'signed_date' => '2023-01-01',
    'agree_terms' => 'on',
    'security' => wp_create_nonce('rpa_deal_form_nonce')
];

try {
    do_action('wp_ajax_rpa_submit_deal_form');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
