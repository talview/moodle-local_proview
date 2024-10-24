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
 * @uses       die
 */

namespace local_proview\local\api;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * Guniversal analytics class.
 * @copyright  Talview, 2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tracker
{
    /**
     * Insert the actual tracking code.
     *
     * @return void As the insertion is done through the {js} template API.
     */

    public static function fetchSecureToken($external_session_id, $external_attendee_id)
    {
        $curl = new \curl();
        $api_base_url = trim(get_config('quizaccess_proctor', 'proview_callback_url'));
        $auth_payload = new \stdClass();
        $auth_payload->username = trim(get_config('quizaccess_proctor', 'proview_admin_username'));
        $auth_payload->password = trim(get_config('quizaccess_proctor', 'proview_admin_password'));
        $auth_response = self::generate_auth_token($api_base_url, $auth_payload);
        $auth_token = $auth_response['access_token'];
        $proctor_token = trim(get_config('local_proview', 'token'));
        $url = $api_base_url . '/token/playback';
        $data = array(
            'proctor_token' => $proctor_token,
            'validity' => 120,
            'external_session_id' => $external_session_id,
            'external_attendee_id' => $external_attendee_id
        );
        try {
            $curl->setHeader(array('Content-Type: application/json', 'Authorization: Bearer ' . $auth_token));
            $response = $curl->post($url, json_encode($data));
            $decoded_response = json_decode($response, true);
            return $decoded_response;
        } catch (\Throwable $err) {
            self::capture_error($err);
        }
    }

    public static function storeFallbackDetails($attempt_no, $proview_url, $proctor_type, $user_id, $quiz_id)
    {
        global $DB;
        $response = $DB->insert_record('local_proview', [
            "quiz_id" => $quiz_id,
            "proview_url" => $proview_url,
            "user_id" => $user_id,
            "attempt_no" => $attempt_no,
            "proctor_type" => $proctor_type,
        ]);
        return $response;
    }
    private static function redirect_to_wrapper($proctoring_payload, $quiz)
    {
        // TODO Add check if wrapper URL already exists
        $wrapper_response = self::create_sb_wrapper($proctoring_payload, $quiz);
        redirect($wrapper_response->signed_short_url);
        return;
    }

    private static function create_sb_wrapper($proctoring_payload, $quiz)
    {
        global $PAGE;
        $curl = new \curl();
        $api_base_url = trim(get_config('quizaccess_proctor', 'proview_callback_url'));
        $auth_payload = new \stdClass();
        $auth_payload->username = trim(get_config('quizaccess_proctor', 'proview_admin_username'));
        $auth_payload->password = trim(get_config('quizaccess_proctor', 'proview_admin_password'));
        $auth_response = self::generate_auth_token($api_base_url, $auth_payload);
        $auth_token = $auth_response['access_token'];
        $url = $api_base_url . '/proview/wrapper/create';
        $data = array(
            'session_external_id' => $proctoring_payload->session_id,
            'attendee_external_id' => $proctoring_payload->profile_id,
            'redirect_url' => $PAGE->url->__toString(),
            'expiry' => date(DATE_ISO8601, $quiz->timeclose == 0 ? strtotime("+3 days") : $quiz->timeclose ),
            'is_secure_browser' => true
        );
        var_dump($data);
        try {
            $curl->setHeader(array('Content-Type: application/json', 'Authorization: Bearer ' . $auth_token));
            $response = $curl->post($url, json_encode($data));
            $decoded_response = json_decode($response, false);
            return $decoded_response;
        } catch (\Throwable $err) {
            self::capture_error($err);
        }
    }



    private static function generate_auth_token($api_base_url, $payload)
    {
        $curl = new \curl();
        $headers = array('Content-Type: application/json');
        $curl->setHeader($headers);
        $request_url = $api_base_url . '/auth';
        $json_payload = json_encode($payload);
        try {
            $response = $curl->post($request_url, $json_payload);
            if ($curl->get_errno()) {
                $error_msg = $curl->error;
                throw new moodle_exception('errorapirequest', 'quizaccess_proctor', '', $error_msg);
            }
            $decoded_response = json_decode($response, true);
            if (!isset($decoded_response['access_token'])) {
                throw new CustomException("Auth Token Not generated");
            }
            return $decoded_response;
        } catch (\Throwable $err) {
            self::capture_error($err);
        }
    }

    private static function capture_error(\Throwable $err)
    {
        \Sentry\init(['dsn' => 'https://61facdc5414c4c73ab2b17fe902bf9ba@o286634.ingest.sentry.io/5304587']);
        \Sentry\captureException($err);
    }

    public static function insert_tracking()
    {
        global $PAGE, $OUTPUT, $USER, $DB;

        $pageinfo = get_context_info_array($PAGE->context->id);
        $template = new stdClass();
        $template->proview_url = trim(get_config('local_proview', 'proview_url'));
        $template->token = trim(get_config('local_proview', 'token'));
        $template->enabled = trim(get_config('local_proview', 'enabled'));
        $template->root_dir = trim(get_config('local_proview', 'root_dir'));
        $template->profile_id = $USER->id;
        $template->proview_callback_url = trim(get_config('quizaccess_proctor', 'proview_callback_url'));
        $template->proview_playback_url = trim(get_config('local_proview', 'proview_playback_url'));
        $cm = $PAGE->cm;
        if ($cm && $cm->instance) {
            $quiz = $DB->get_record('quiz', array('id' => $cm->instance));      // Fetching current quiz data for password.
            $template->quiz_password = $quiz->password;
            $template->quiz_id = $quiz->id;

            $attempt = $DB->get_record('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $USER->id, 'state' => 'inprogress'));

            if (!$attempt) {
                $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $USER->id));
                $attempt = $attempts ? max(array_filter(array_column($attempts, 'attempt'))) : 0;
                $attempt += 1;
            } else {
                $attempt = $attempt->attempt;
            }
            $template->current_attempt = $attempt;
            $quizaccess_proctor_setting = $DB->get_record('quizaccess_proctor', array('quizid' => $quiz->id));
            if ($quizaccess_proctor_setting) {
                $template->session_type = $quizaccess_proctor_setting->proctortype;
            } else {
                $template->session_type = "ai_proctor";
            }
            $template->session_id = $template->session_type === "live_proctor" ? $quiz->id.'-'.$USER->id : $quiz->id.'-'.$USER->id.'-'.$attempt;
            if (strpos($PAGE->url, ('mod/quiz/attempt')) &&
                $quizaccess_proctor_setting && 
                $quizaccess_proctor_setting->proctortype == 'noproctor' && 
                $quizaccess_proctor_setting->tsbenabled && 
                strpos($_SERVER ['HTTP_USER_AGENT'], "Proview-SB") === FALSE) {
                self::redirect_to_wrapper($template, $quiz);
                return;
            }

            if (strpos($PAGE->url, ('mod/quiz/report'))) {
                $quiz_attempts = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id));
                foreach ($quiz_attempts as $quiz_attempt) {
                    $local_proview_data = $DB->get_record('local_proview', array('quiz_id' => $quiz->id, 'attempt_no' => $quiz_attempt->id), 'proview_url,proctor_type,attempt_no');
                    $quiz_attempt->proview_url = isset($local_proview_data->proview_url) ? $local_proview_data->proview_url : '';
                    $quiz_attempt->proctor_type = isset($local_proview_data->proctor_type) ? $local_proview_data->proctor_type : $DB->get_record('quizaccess_proctor', array('quizid' => $quiz->id), 'proctortype')->proctortype;
                    $quiz_attempt->attempt_no = $quiz_attempt->attempt;
                }
                $template->attempts = json_encode($quiz_attempts);
            }
        }

        if ($pageinfo && !empty($template->token)) {
            // The templates only contains a "{js}" block; so we don't care about
            // the output; only that the $PAGE->requires are filled.
            $OUTPUT->render_from_template('local_proview/tracker', $template);
        }
    }
}

