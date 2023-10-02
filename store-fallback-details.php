<?php
use local_proview\local\api\tracker;
require_once('../../config.php');
global $CFG;
if (isset($_POST['action']) && $_POST['action'] === 'store_fallback_details') {
    $attempt_no = $_POST['attempt_no'];
    $proview_url = $_POST['proview_url'];
    $proctor_type = $_POST['proctor_type'];
    $user_id = $_POST['user_id'];
    $quiz_id = $_POST['quiz_id'];
    $response = tracker::storeFallbackDetails($attempt_no, $proview_url, $proctor_type, $user_id, $quiz_id);
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
