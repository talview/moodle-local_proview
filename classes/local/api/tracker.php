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

    private static function generate_auth_token($api_base_url, $payload)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $api_base_url . '/auth',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
        ]);
        try {
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($err) {
                throw new CustomException($err);
            } elseif ($response && $httpcode != 200) {
                throw new CustomException($response);
            } else {
                return $response;
            }
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
        $template->proview_url = get_config('local_proview', 'proview_url');
        $template->token = get_config('local_proview', 'token');
        $template->enabled = get_config('local_proview', 'enabled');
        $template->root_dir = get_config('local_proview', 'root_dir');
        $template->profile_id = $USER->id;
        $template->proview_callback_url = get_config('quizaccess_proctor', 'proview_callback_url');


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
            $api_base_url = trim(get_config('quizaccess_proctor', 'proview_callback_url'));
            $auth_payload = new \stdClass();
            $auth_payload->username = trim(get_config('quizaccess_proctor', 'proview_admin_username'));
            $auth_payload->password = trim(get_config('quizaccess_proctor', 'proview_admin_password'));
            $auth_response = self::generate_auth_token($api_base_url, $auth_payload);
            $template->auth_token = json_decode($auth_response)->access_token;

            if (strpos($PAGE->url, ('mod/quiz/report'))) {
                $attempts = $DB->get_records('local_proview', array('quiz_id' => $quiz->id), 'attempt_no', 'attempt_no,proview_url,quiz_id,user_id,proctor_type');
                foreach ($attempts as $attempt) {
                    $noOfAttempts=$DB->get_records('quiz_attempts', array('id' => $attempt->attempt_no));
                    $attempt->attempt_no = $noOfAttempts ? max(array_filter(array_column($noOfAttempts, 'attempt'))) : 0;
                }
                $template->attempts = json_encode($attempts);
            }
        }
        if ($pageinfo && !empty($template->token)) {
            // The templates only contains a "{js}" block; so we don't care about
            // the output; only that the $PAGE->requires are filled.
            $OUTPUT->render_from_template('local_proview/tracker', $template);
        }

    }
}

