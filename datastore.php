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
 * @author     Mani Ka <mani@talview.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_login();

// Include config.php.
// @codingStandardsIgnoreStart
// Let codechecker ignore the next line because otherwise it would complain about a missing login check
// after requiring config.php which is really not needed.
require_once('../../config.php');
// @codingStandardsIgnoreEnd

// Include lib.php.
// require_once(__DIR__ . '/lib.php');

// Globals.
global $DB;

$post = json_decode(file_get_contents('php://input'));

$attempt = $DB->get_record('quiz_attempts', array('quiz' => $post->quiz_id, 'userid' => $post->user_id,'state' => 'inprogress'));

if($attempt && $attempt->id){

    // Inserting attempt data in local_proview table
    $response = $DB->insert_record('local_proview', [
                        "quiz_id"=>$post->quiz_id,
                        "proview_url"=>$post->proview_url,
                        "course_id"=>$post->course_id,
                        "user_id"=>$post->user_id,
                        "attempt_no"=>$attempt->id
                    ]);
    
    print $response;
    return; 
}
http_response_code(404);
print "Attempt not found";

?>  