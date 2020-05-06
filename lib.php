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
 * Proview
 *
 * This module provides support for remote proctoring quizzes and assessments using Proview
 *
 * @package    local_proview
 * @copyright  Talview, 2020
 * @author     Mani Ka <mani@talview.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use local_proview\injector;

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_proview_before_footer() {
    injector::inject();
}

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_proview_before_http_headers() {
    injector::inject();
    if(!headers_sent()) {
        @header_remove('Feature-Policy');
        @header("Feature-Policy: vibrate 'none'; ambient-light-sensor 'none';");
    }

}