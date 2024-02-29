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
 * Privacy Subsystem implementation for local_proview.
 *
 * @package    local_proview
 * @copyright  2020 Talview <privacy@talview.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @uses       die
 */

namespace local_proview\privacy;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/moodlelib.php');

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\contextlist;
use stdClass;

/**
 * Privacy Subsystem implementation for local_proview.
 * @copyright  2020 Talview <privacy@talview.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\data_provider,
        \core_privacy\local\request\core_user_data_provider,
        \core_privacy\local\request\core_userlist_provider
        {

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'local_proview',
             [
                'user_id' => 'privacy:metadata:local_proview:user_id',
                'quiz_id' => 'privacy:metadata:local_proview:quiz_id',
                'attempt_no' => 'privacy:metadata:local_proview:attempt_no',
                'proview_url' => 'privacy:metadata:local_proview:proview_url'
             ],
            'privacy:metadata:local_proview'
        );

        $collection->add_external_location_link('proview', [
            'ipaddress' => 'privacy:metadata:talview_proview:ipaddress',
            'candidate_video' => 'privacy:metadata:talview_proview:candidate_video',
            'candidate_audio' => 'privacy:metadata:talview_proview:candidate_audio',
            'candidate_photo' => 'privacy:metadata:talview_proview:candidate_photo',
            'candidate_idcard' => 'privacy:metadata:talview_proview:candidate_idcard',
            'browser' => 'privacy:metadata:talview_proview:browser',
            'operating_system' => 'privacy:metadata:talview_proview:operating_system',
            'profile_id' => 'privacy:metadata:talview_proview:profile_id',
            'session_id' => 'privacy:metadata:talview_proview:session_id',
        ], 'privacy:metadata:talview_proview');

        return $collection;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist) {

        global $DB;
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'instanceid'    => $context->instanceid,
            'modulename'    => 'quiz',
        ];

        // Candidates who attempted the quiz.
        $sql = "SELECT lp.user_id
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                JOIN {quiz} q ON q.id = cm.instance
                JOIN {local_proview} lp ON lp.quiz_id = q.id
                WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        // Fetch all forum discussions, and forum posts.
        $sql = "SELECT c.id
                    FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {quiz} q ON q.id = cm.instance
                LEFT JOIN {local_proview} lp ON lp.quiz_id = q.id
                WHERE lp.user_id = :localproviewuserid
        ";

        $params = [
            'modname'            => 'quiz',
            'contextlevel'       => CONTEXT_MODULE,
            'localproviewuserid' => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;

    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB, $CFG;
        if (empty($contextlist->count())) {
            return;
        }
        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT lp.id,
                       lp.user_id,
                       lp.quiz_id,
                       lp.attempt_no,
                       c.id as context_id
                  FROM {local_proview} lp
                  JOIN {quiz} q ON lp.quiz_id = q.id
                  JOIN {course_modules} cm ON q.id = cm.instance
                  JOIN {context} c ON cm.id = c.instanceid
                 WHERE c.id {$contextsql}
                   AND lp.user_id = :userid
               ORDER BY lp.id, cm.id, lp.attempt_no";
        $params = [
            'userid' => $user->id,
        ] + $contextparams;
        $lpmaps = $DB->get_records_sql($sql, $params);
        foreach ($contextlist->get_contexts() as $context) {
            $lpmapstd = new stdClass();
            $i = 1;
            foreach ($lpmaps as $index => $lpmap) {
                if ($lpmap->context_id == $context->id) {
                    $id = $i.".";
                    $lpmapstd->$id = $lpmap;
                    unset($lpmaps[$index]);
                }
                $i += 1;
            }
            writer::with_context($context)
                ->export_data(["Proview"], $lpmapstd);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        global $DB, $USER;
        $userids = $userlist->get_userids();

        if (empty($userids)) {
            return;
        }

        $token = get_config('local_proview', 'token');
        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['quiz_id' => $quiz->id], $userinparams);
        $sql = "quiz_id = :quiz_id AND user_id {$userinsql}";

        $to = array();
        $reply = array();
        $from = array();

        $to[0] = array("support@talview.com", "Talview Support");
        $to[1] = array($USER->email, fullname($USER, true));
        $reply[0] = array($USER->email, fullname($USER, true));
        $from = self::get_from_user();

        $txt = "As per GDPR Compliance, Please Delete the Proview data for the following details\nProfile ID: ( ";
        foreach ($userids as $index => $userid) {
            if ($index === 0) {
                $txt .= $userid;
            } else {
                $txt .= ", {$userid}";
            }
        }
        $txt .= ")\nSession ID is like ( {$quiz->id}-* )";

        self::send_mail($to, $reply, $from, $txt);
        $DB->delete_records_select('local_proview', $sql, $params);
    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB, $USER;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $token = get_config('local_proview', 'token');
        $quizid = $cm->instance;
        $to = array();
        $reply = array();
        $from = array();

        $to[0] = array("support@talview.com", "Talview Support");
        $to[1] = array($USER->email, fullname($USER, true));
        $reply[0] = array($USER->email, fullname($USER, true));
        $from = self::get_from_user();

        $txt = "As per GDPR Compliance, Please Delete the Proview data for all the sessions with\n";
        $txt .= "Session ID is like ( {$quizid}-* )";

        self::send_mail($to, $reply, $from, $txt);
        $DB->delete_records('local_proview', ['quiz_id' => $quizid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $token = get_config('local_proview', 'token');

        $quizids = array();
        $to = array();
        $reply = array ();
        $from = array();

        $to[0] = array("support@talview.com", "Talview Support");
        $to[1] = array($user->email, fullname($user, true));
        $reply[0] = array($user->email, fullname($user, true));
        $from = self::get_from_user();

        foreach ($contextlist->get_contexts() as $context) {
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            array_push($quizids, $instanceid);
            $DB->delete_records('local_proview', ['quiz_id' => $instanceid, 'user_id' => $user->id]);
        }

        $txt = "As per GDPR Compliance, Please Delete the Proview data for the following details\n";
        $txt .= "Profile ID: {$user->id}\nSession ID is like ( ";
        foreach ($quizids as $index => $quizid) {
            if ($index === 0) {
                $txt .= "{$quizid}-*";
            } else {
                $txt .= ", {$quizid}-*";
            }
        }
        $txt .= " )";

        self::send_mail($to, $reply, $from, $txt);
    }


    /**
     * Fetch email ID and name for From field
     *
     * Priority level based on which email address is selected to send the email is as follows:
     * noreply > smtpuser > admin > default (noreply@moodle.com)
     *
     * @return array Array is returned with sender email address in cell 0 and sender name in cell 1
     */
    private static function get_from_user() : array {
        global $CFG;
        $primaryadmin = get_admin();
        $noreply = empty($CFG->noreplyaddress) ? 'noreply@' . get_host_from_url($CFG->wwwroot) : $CFG->noreplyaddress;

        $fromemail = "";
        $fromname = "";
        if (!validate_email($noreply)) {
            if (!validate_email($CFG->smtpuser)) {
                if (!validate_email($primaryadmin->email)) {
                    $fromemail = "noreply@moodle.com";
                    $fromname = "Moodle";
                } else {
                    $fromemail = $primaryadmin->email;
                    $fromname = fullname($primaryadmin, true);
                }
            } else {
                $fromemail = $CFG->smtpuser;
                $fromname = "Moodle";
            }
        } else {
            $fromemail = $noreply;
            $fromname = "No Reply";
        }
        return array($fromemail, $fromname);
    }

    /**
     * Send Email to support@talview.com to raise a GDPR Request to delete data from Talviews Server
     *
     * @param array $to An array cokntaining To Addresses
     * @param array $reply An array containing Reply to addresses
     * @param array $from An array containing from addresses
     * @param string $txt The main body of the mail in plain text
     * @return bool true if mail is sent otherwise false
     */
    private static function send_mail(array $to, array $reply, array $from, string $txt) : bool {
        $sitename = get_site();
        $token = get_config('local_proview', 'token');
        $account = get_config('local_proview', 'proview_acc_name');
        if ($account == '') {
            $account = $sitename->fullname;
        }
        $subject = "GDPR Request from Moodle | {$account}";

        $mail = get_mailer();
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $txt;
        $mail->From = $from[0];
        $mail->FromName = $from[1];

        foreach ($to as $toemail) {
            $mail->addAddress($toemail[0], $toemail[1]);
        }
        foreach ($reply as $replyemail) {
            $mail->addReplyTo($replyemail[0], $replyemail[1]);
        }

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        }
        return true;
    }
}
