<?php
use local_proview\local\api\tracker;
require_once('../../config.php');
global $CFG;
if (isset($_POST['action']) && $_POST['action'] === 'fetch_secure_token') {
    $external_session_id = $_POST['external_session_id'];
    $external_attendee_id = $_POST['external_attendee_id'];
    $token_response = tracker::fetchSecureToken( $external_session_id, $external_attendee_id);
    header('Content-Type: application/json');
    echo json_encode($token_response);
} else {
       http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
