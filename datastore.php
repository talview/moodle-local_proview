<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Proview
 *
 * This module provides support for remote proctoring quizzes and assessments using Proview
 *
 * @package    local_proview
 * @copyright  Talview, 2020
 * @author     Talview Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
// @codingStandardsIgnoreStart
// Let codechecker ignore the next line because otherwise it would complain about a missing login check
// after requiring config.php which is really not needed.
require_once('../../config.php');
// @codingStandardsIgnoreEnd

// Globals.
global $DB, $USER, $PAGE;

$post = json_decode(file_get_contents('php://input'));

if ($post && ($post->sesskey == sesskey())) {
    $attempt = $DB->get_record('quiz_attempts',
        array('quiz' => $post->quiz_id, 'userid' => $post->user_id, 'state' => 'inprogress'));
    if ($attempt && $attempt->id) {
        // Inserting attempt data in local_proview table.
        $response = $DB->insert_record('local_proview', [
                            "quiz_id" => $post->quiz_id,
                            "proview_url" => $post->proview_url,
                            "user_id" => $USER->id,
                            "attempt_no" => $attempt->id,
                            "proctor_type" => $post->proctor_type,
                        ]);
        print $response;
        return;
    }
    http_response_code(404);
    print "Attempt not found";
}

$query = $_SERVER['QUERY_STRING'];

$quizid = explode('=', explode('&', $query)[0])[1];
$sesskey = explode('=', explode('&', $query)[1])[1];

$template = new stdClass();
$quizaccess_proctor_setting = null;
if ($sesskey == sesskey()) {
    $quiz = $DB->get_record('quiz', array('id' => $quizid));      // Fetching current quiz data for password.

    $attempt = $DB->get_record('quiz_attempts', array('quiz' => $quizid, 'userid' => $USER->id, 'state' => 'inprogress'));
    if (!$attempt) {
        $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid, 'userid' => $USER->id));
        $attempt = $attempts ? max(array_filter(array_column($attempts, 'attempt'))) : 0;
        $attempt += 1;
    } else {
        $attempt = $attempt->attempt;
    }
    if (get_config('quizaccess_proctor', 'enableproctor')) {
    // if (\core_component::get_component_directory('quizaccess_proctor')) {
        $template->plugin_installed = true;
        $quizaccess_proctor_setting = $DB->get_record('quizaccess_proctor', array('quizid' => $quiz->id));
    }
    if ($quizaccess_proctor_setting) {
        $template->session_type = $quizaccess_proctor_setting->proctortype;
    } else {
        $template->session_type = "ai_proctor";
    }
    $template->quiz_password = ($quiz->password ? $quiz->password : null);
    $template->profile_id = $USER->id;
    $template->instructions = $quizaccess_proctor_setting->instructions;
    $template->reference_link= $quizaccess_proctor_setting->reference_link;
    $template->session_id = $template->session_type === "live_proctor" ? $quizid.'-'.$USER->id : $quizid.'-'.$USER->id.'-'.$attempt;   // Do not append attempt number for live proctoring. Re-attempting same quiz not supported in live proctoring.
    $template->proview_url = trim(get_config('local_proview', 'proview_url'));
    $template->token = trim(get_config('local_proview', 'token'));
    echo json_encode($template);
    return;
}

http_response_code(401);
print "Invalid sesskey";