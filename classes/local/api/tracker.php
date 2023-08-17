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
            $session_id=$template->quiz_id."-".$template->profile_id."-".$template->current_attempt;
            $attendee_id=$template->profile_id;
            if (strpos($PAGE->url, ('mod/quiz/report'))) {
                $attempts = $DB->get_records('local_proview', array('quiz_id' => $quiz->id), 'attempt_no', 'attempt_no,proview_url');
                foreach ($attempts as $attempt) {
                    $token=self::generate_proview_token($template->token,$session_id,$attendee_id);
                    $attempt->proview_url = $attempt->proview_url."?token=".$token;
                    var_dump($attempt);

                }// Fetching all the records (for proview url) for a given quiz.
                $template->attempts = json_encode($attempts);

            }
        }

        if ($pageinfo && !empty($template->token)) {
            // The templates only contains a "{js}" block; so we don't care about
            // the output; only that the $PAGE->requires are filled.
            $OUTPUT->render_from_template('local_proview/tracker', $template);
        }

    }
    public static function generate_proview_token($token,$session_id,$attendee_id) {
        $curl = curl_init();
//        $token="4d1ca44a-dd19-4052-b2ee-0ae600c6c688";
//        $session_id="19-2-1";
//        $attendee_id="189152";

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://proctoring.talview.com/v1/token/playback",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "proctor_token" => $token,
                "validity" => 240,
                "external_session_id" => $session_id,
                "external_attendee_id" => $attendee_id
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "app-id: b37ec896-f62b-4cbe-b39f-8dd21881dfd3",
                "is-universal-proview: true"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return null;
        } else {
            $response_data = json_decode($response, true);
            return $response_data["token"];
        }
    }
}

