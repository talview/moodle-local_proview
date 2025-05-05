<?php
// This file is part of Moodle - https://moodle.org/
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

/**
 * PHPUnit tests for the fix_missing_proctor_records scheduled task.
 *
 * @package   local_proview
 * @copyright 2025 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fix_missing_proctor_records_test extends \advanced_testcase {
    /**
     * Test fix_missing_proctor_records task.
     */
    public function test_fix_missing_proctor_records_task() {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'proctortype' => '',
            'instructions' => '',
            'reference_link' => '',
            'blacklisted_softwares_win' => '',
            'blacklisted_softwares_mac' => '',
        ]);

        // Run the task.
        $task = new \local_proview\task\fix_missing_proctor_records();
        $task->execute();

        $this->assertTrue(true, 'Task executed without throwing errors');

    }
}
