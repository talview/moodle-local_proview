<?php
// This file is part of the Local Proview plugin for Moodle
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
 * Class injector
 *
 * @package     local_proview
 * @author      Talview Inc.
 * @copyright   2020 Talview Inc
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_proview\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/pagelib.php');
require_once($CFG->dirroot . '/local/proview/vendor/autoload.php');

/**
 * Class injector
 *
 * @package     local_proview
 * @author      Talview Inc.
 * @copyright   2020 Talview Inc
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @uses        die
 */
class injector {
    /** @var bool Keeps state for injection */
    private static $injected = false;

    /**
     * Inject the password of the quiz if the quiz is password protected.
     *
     * @param moodle_page $PAGE Moodle PAGE class
     * @param stdClass $quiz Moodle quiz class
     * @return null
     */
    private static function inject_password($PAGE, $quiz) {
        if ($quiz->password) {                          // If the quiz is password protected then inject the js.
            $PAGE->requires->js_call_amd('local_proview/proview', 'init', array($quiz->password));
        }
    }

    /**
     * Do the actual analytics code injection.
     *
     * @return null
     */
    public static function inject() {
        global $USER, $COURSE, $DB, $PAGE;
        $page_path =  $PAGE->url->get_path();
        if (!preg_match('/mod\/quiz\/(attempt|summary|startattempt|view|report)/',$page_path )) {
            return;
        }
        $enabled = get_config('local_proview', 'enabled');
        $string_match = get_config('local_proview', 'string_match');
        $quizaccess_proctor_setting_enabled = get_config('quizaccess_proctor', 'enableproctor');
        if (!$enabled) {
            return;
        }

        // Logic for enabling proview for course level and quiz level starts.
        try {
            $list = new \core_course_list_element($COURSE);
            $datas = $list->get_custom_fields();
            $courselevelconfiguration = 0;                   // Field for storing course level configuration.
            foreach ($datas as $data) {
                if (empty($data->get_value())) {
                    continue;
                }
                if (strpos(($data->get_field()->get('name')), "Proview Configuration") !== false) {
                    $courselevelconfiguration = $data->get_value();
                }
            }
            if ($PAGE->cm) {
                $quiz = $DB->get_record('quiz', array('id' => $PAGE->cm->instance));
                if ($quizaccess_proctor_setting_enabled) {
                // if (\core_component::get_component_directory('quizaccess_proctor')) {
                    $quizaccess_proctor_setting = $DB->get_record('quizaccess_proctor', array('quizid' => $quiz->id));
                    if ($quizaccess_proctor_setting) {
                        $courselevelconfiguration = 4;
                    }
                }
                switch ($courselevelconfiguration) {
                    case 1:      // Proview Enabled for complete course.
                        break;
                    case 2:      // Proview Enabled for specific quizes.
                        if ($quiz && $quiz->id) {
                            if (!$string_match) {
                                self::inject_password($PAGE, $quiz);
                                return; 
                            }
                            if (!stripos (json_encode($quiz->name), "Proctor")) {
                                self::inject_password($PAGE, $quiz);
                                return;
                            }
                        }
                        break;
                    case 3:     // Proview disabled for complete course.
                        self::inject_password($PAGE, $quiz);
                        return;
                        break;
                    case 4:     // Additional Plugin Added for proctoring Settings.
                        if ($quizaccess_proctor_setting && $quizaccess_proctor_setting->proctortype == 'noproctor') {
                            self::inject_password($PAGE, $quiz);
                            return;
                        }
                        break;
                    default:    // If course level configuration is not enabled then Quiz level configuration is enabled by default.
                        if ($quiz && $quiz->id) {
                            if (!$string_match) {
                                self::inject_password($PAGE, $quiz);
                                return; 
                            }
                            if (!stripos (json_encode($quiz->name), "Proctor")) {
                                self::inject_password($PAGE, $quiz);
                                return;
                            }
                        }
                        break;
                }
            }
            // Logic for enabling proview for course level and quiz level ends.

            if ($COURSE && $COURSE->id) {
                // Logic for enabling specific user to use proctored assessment STARTS
                // Fetching the group details for the proview_disabled group.
                $groupdetails = $DB->get_record('groups', ['courseid' => $COURSE->id, 'name' => 'proview_disabled']);

                if (!empty($groupdetails)) {

                    $groupmember = $DB->get_record('groups_members', ['groupid' => $groupdetails->id, 'userid' => $USER->id]);// Request to check blacklist.

                    if ($groupmember) {
                        $cm = $PAGE->cm;
                        if ($cm) {
                            $quiz = $DB->get_record('quiz', array('id' => $cm->instance));

                            self::inject_password($PAGE, $quiz);
                        }
                        return;
                    }
                }
                // Logic for enabling specific user to use proctored assessment ENDS.

                // // Logic for enabling Talview Safe Exam Browser if proctoring is enabled and quiz title contains TSB keyword STARTS
                // if ($PAGE->cm) {
                //     $quiz = $DB->get_record('quiz', array('id' => $PAGE->cm->instance));
                //     // print $PAGE->url."\n";
                //     if ((strpos ($PAGE->url, ('mod/quiz/attempt')) !== FALSE 
                //             || strpos ($PAGE->url, ('mod/quiz/summary')) !== FALSE) 
                //         && (($quizaccess_proctor_setting_enabled 
                //                 && $quizaccess_proctor_setting->tsbenabled) 
                //             || (!$quizaccess_proctor_setting_enabled 
                //                 && $string_match 
                //                 && strpos ($quiz->name, ('[TSB]')) !== FALSE))
                //         && $_SERVER ['HTTP_USER_AGENT'] != "Proview-SB") {
                //         // echo $_SERVER ['HTTP_USER_AGENT'];
                //         $tsbURL = "tsb://".explode("://",$PAGE->url)[1];
                //         if (!headers_sent()) {
                //             header('Location: '.$tsbURL);
                //         } else {
                //             echo ("<script>location.href='$tsbURL'</script>");
                //         }
                //         die;
                //     }
                // }
                // // Logic for enabling Talview Safe Exam Browser if proctoring is enabled and quiz title contains TSB keyword ENDS
                if (self::$injected) {
                    return;
                }
                self::$injected = true;
            }

            $t = new api\tracker();
            $t::insert_tracking();
            return;
        } catch (\Throwable $error) {
            \Sentry\init(['dsn' => 'https://61facdc5414c4c73ab2b17fe902bf9ba@o286634.ingest.sentry.io/5304587' ]);
            \Sentry\captureException($error);
            die;
            ?>
            <script>
                document.body.style.margin = '0px';
                document.body.innerHTML = `<iframe id="errorIFrame"
                        src='https://pages.talview.com/proview/error/index.html'
                        title="Proview Error"
                        style="width: 100%;
                        height:100%;
                        border: 0px;">
                    <p>Your browser does not support iframes</p>
                </iframe>`;
            </script>
            <?php
            die;
        }
    }

    /**
     * Toggle the state back to un-injected.
     *
     * @return null
     */
    public static function reset() {
        self::$injected = false;
        return;
    }
}
