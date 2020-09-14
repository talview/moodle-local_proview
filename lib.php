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
 * @author     Talview Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @uses       die
 */

defined('MOODLE_INTERNAL') || die;

use local_proview\injector;

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_proview_before_standard_top_of_body_html() {

}

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_proview_before_http_headers() {

}

function local_proview_before_footer() {
    injector::inject();
}