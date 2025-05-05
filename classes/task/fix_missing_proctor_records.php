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
use stdClass;

/**
 * Scheduled tasks to fix the missing quiz records from {quizaccess_proctor}
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
            SELECT
                q.id AS quizid,
                cm.id AS cmid
            FROM
                {quiz} q
            LEFT JOIN
                {course_modules} cm ON cm.instance = q.id AND cm.module = (
                    SELECT id FROM {modules} WHERE name = :modulename
                )
            LEFT JOIN
                {quizaccess_proctor} p ON q.id = p.quizid
            WHERE
                p.quizid IS NULL
            ";

            $params = ['modulename' => 'quiz'];
            $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $data = new stdClass();
            $data->quizid = $record->quizid;
            $data->cmid = $record->cmid;
            $data->proctortype = 'noproctor';
            $data->tsbenabled = 0;
            $data->usermodified = 2;
            $data->timecreated = $timecreated;
            $data->timemodified = 0;
            $data->reference_link = '';
            $data->instructions = '';
            $data->blacklisted_softwares_win = '';
            $data->blacklisted_softwares_mac = '';
            $data->sb_kiosk_mode = 0;
            $data->sb_content_protection = 0;

            $DB->insert_record('quizaccess_proctor', $data);
        }
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
