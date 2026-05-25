<?php
// meta_webhook.php
require_once 'db_connect.php';
require_once 'meta_config.php';

// 1. Webhook Verification (Facebook requirement)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe' && 
        isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $meta_verify_token) {
        echo $_GET['hub_challenge'];
        exit;
    }
}

// 2. Handling Lead Notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['entry'])) {
        foreach ($input['entry'] as $entry) {
            foreach ($entry['changes'] as $change) {
                if ($change['field'] === 'leadgen') {
                    $leadgen_id = $change['value']['leadgen_id'];
                    $page_id = $change['value']['page_id'];
                    $form_id = $change['value']['form_id'];
                    $created_time = date('Y-m-d H:i:s', $change['value']['created_time']);

                    // Fetch lead details from Meta Graph API
                    fetchAndStoreLead($leadgen_id, $page_id, $form_id, $created_time);
                }
            }
        }
    }
}

function fetchAndStoreLead($lead_id, $page_id, $form_id, $time) {
    global $conn, $meta_page_access_token;

    // Meta Graph API URL for Lead details
    $url = "https://graph.facebook.com/v19.0/{$lead_id}?access_token={$meta_page_access_token}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['field_data'])) {
        $full_name = '';
        $phone_number = '';
        $email = '';

        foreach ($data['field_data'] as $field) {
            if ($field['name'] === 'full_name') $full_name = $field['values'][0];
            if ($field['name'] === 'phone_number') $phone_number = $field['values'][0];
            if ($field['name'] === 'email') $email = $field['values'][0];
        }

        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO meta_leads (lead_id, form_id, page_id, full_name, phone_number, email, lead_data, created_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $lead_id, 
                $form_id, 
                $page_id, 
                $full_name, 
                $phone_number, 
                $email, 
                $response, 
                $time
            ]);
        } catch (PDOException $e) {
            error_log("DB Error in Meta Webhook: " . $e->getMessage());
        }
    }
}
?>
