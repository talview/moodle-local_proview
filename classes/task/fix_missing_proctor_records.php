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

namespace local_proview\task;

use core\task\scheduled_task;

/**
 * Scheduled tasks to fix the missing quiz records from mdl_quizaccess_proctor
 *
 * @package   local_proview
 * @copyright 2025 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fix_missing_proctor_records extends scheduled_task {
    /**
     * Run the task.
     */
    public function execute() {
        global $DB;

        $timecreated = time();

        $sql = "
            INSERT INTO {quizaccess_proctor} (
                quizid, cmid, proctortype, tsbenabled, usermodified, timecreated, timemodified,
                reference_link, instructions, blacklisted_softwares_win, blacklisted_softwares_mac,
                sb_kiosk_mode, sb_content_protection
            )
            SELECT
                q.id AS quizid,
                cm.id AS cmid,
                'noproctor' AS proctortype,
                0 AS tsbenabled,
                2 AS usermodified,
                :timecreated AS timecreated,
                0 AS timemodified,
                '' AS reference_link,
                '' AS instructions,
                '' AS blacklisted_softwares_win,
                '' AS blacklisted_softwares_mac,
                0 AS sb_kiosk_mode,
                0 AS sb_content_protection
            FROM
                {quiz} q
            LEFT JOIN
                {course_modules} cm ON cm.instance = q.id AND cm.module = (
                    SELECT id FROM mdl_modules WHERE name = 'quiz'
                )
            LEFT JOIN
                {quizaccess_proctor} p ON q.id = p.quizid
            WHERE
                p.quizid IS NULL
        ";

        $DB->execute($sql, ['timecreated' => $timecreated]);
    }

    /**
     * Get the name of the task for use in the interface.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskname:fix_missing_proctor_records', 'local_proview');
    }
}
