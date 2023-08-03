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
 */

$string['pluginname'] = 'Proview';
$string['proview_url'] = 'Proview Endpoint URL';
$string['proview_url_desc'] = 'Enter the Proview End point URL';
$string['proview_playback_url'] = 'Proview Playback URL';
$string['proview_playback_url_desc'] = 'Enter the Proview Playback URL provided by Talview';
$string['token'] = 'Proctor Token';
$string['token_desc'] = 'For example: U9021015';
$string['enabled'] = 'Enabled';
$string['enabled_desc'] = 'Enable Proview for Moodle';
$string['auto_password_injection_enabled'] = 'Automatic Password Injection';
$string['auto_password_injection_enabled_desc'] = 'Enable Automatic Password Injection, Enabling this will auto inject quiz password if proview is enabled on admin level';
$string['string_match'] = 'String Matching';
$string['string_match_desc'] = 'Enable this setting to enable Proview on quiz level basis string matching on the Quiz title.<br />
When this setting is enabled following checks will be performed on the quiz title.<br />
If the quiz title contains the "proctor" keyword (case insensitive) then Proview will be enabled for that quiz. <br />
The following checks are performed if Proview is launched for the quiz:
<ol>
<li>If the quiz title contains the "[LP]" keyword (case sensitive) then Live Proctoring will be enabled for that quiz.</li>
<li>If the quiz title contains the "[RR]" keyword (case sensitive) then Record and Review will be enabled for that quiz.</li>
<li>If the quiz title contains the "[AI]" keyword (case sensitive) then AI Proctoring will be enabled for that quiz.</li> </ol>';
$string['proview_acc_name'] = 'Proview Account Name';
$string['proview_acc_name_desc'] = 'Account Name provided by Talview, If not provided use your organisation name.';
$string['root_dir'] = 'Root Dir';
$string['root_dir_desc'] = 'Root Dir';


$string['privacy:metadata:local_proview'] = 'This plugin is developed by the team at Talview Inc and implements “Proview” (which is a proctoring solution developed in Talview) in Moodle LMS.';
$string['privacy:metadata:local_proview:user_id'] = 'The ID of the user who attempted the quiz.';
$string['privacy:metadata:local_proview:quiz_id'] = 'The ID of the quiz the user attempted.';
$string['privacy:metadata:local_proview:attempt_no'] = 'The current attempt no of the user for this quiz (No of tries for this quiz).';
$string['privacy:metadata:local_proview:proview_url'] = 'The url to view and proctor the candidate attempt in proview admin interface.';

$string['privacy:metadata:talview_proview'] = 'The plugin also stores the data out of moodle in Talview\'s Server.';
$string['privacy:metadata:talview_proview:ipaddress'] = 'The IP Address of the user giving the test.';
$string['privacy:metadata:talview_proview:candidate_video'] = 'Video is recorded and stored of the candidate while giving the exam.';
$string['privacy:metadata:talview_proview:candidate_audio'] = 'Audio is recorded and stored of the candidate while giving the exam.';
$string['privacy:metadata:talview_proview:candidate_photo'] = 'Photo is captured of candidate before the exam for validation.';
$string['privacy:metadata:talview_proview:candidate_idcard'] = 'Candidate is asked to capture a photo of ID Card for validation before the exam.';
$string['privacy:metadata:talview_proview:browser'] = 'Browser used by the candidate is captured for debugging purposes.';
$string['privacy:metadata:talview_proview:operating_system'] = 'Operating System used by the candidate is captured for debugging purposes.';
$string['privacy:metadata:talview_proview:profile_id'] = 'The user id of candidate is shared with proview as profile ID.';
$string['privacy:metadata:talview_proview:session_id'] = 'The quiz id of the quiz is shared with proview along with current attempt as session ID.';
