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
 * @author      Mani Ka <mani@talview.com>
 * @copyright   2020 Talview Inc
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_proview;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/pagelib.php');

/**
 * Class injector
 *
 * @package     local_proview
 * @author      Mani Ka <mani@talview.com>
 * @copyright   2020 Talview Inc
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injector {
    /** @var bool Keeps state for injection */
    private static $injected = false;

    /**
     * Do the actual analytics code injection.
     *
     * @return null
     */

    private static function inject_password($PAGE ,$quiz) {
        if($quiz->password) { //if the quiz is password protected then inject the js
            $PAGE->requires->js_call_amd('local_proview/proview', 'init', array($quiz->password));
        }
    }

    public static function inject() {
        global $USER, $COURSE, $DB, $PAGE;

        $enabled = get_config('local_proview', 'enabled');
        if (!$enabled) {
            return;
        }

        // Logic for enabling proview for course level and quiz level starts
        $list = new \core_course_list_element($COURSE);   
        $datas = $list->get_custom_fields();  
        $courseLevConfig ;                                                                  //Field for storing course level configuration
        foreach ($datas as $data) {
            if (empty($data->get_value())) {
                continue;
            }
            if(strpos(($data->get_field()->get('name')),"Proview Configuration") !== false) {
                $courseLevConfig = $data->get_value();
            }
        }
        $quiz = $DB->get_record('quiz', array('id' => $PAGE->cm->instance));
        switch ($courseLevConfig) {
            case 1: break;                                                                  //Proview Enabled for complete course
            case 2: if ($quiz && $quiz->id) {                                               //Proview Enabled for specific quizes
                        if (!stripos (json_encode($quiz->name),"Proctor")){
                            self::inject_password($PAGE, $quiz);
                            return;
                        }
                    }
                    break;
            case 3: self::inject_password($PAGE, $quiz);
                    return;                                                                 // Proview disabled for complete course
                    break;
            default:if ($quiz && $quiz->id) {                                               //If course level configuration is not enabled then Quiz level configuration is enabled by default
                        if (!stripos (json_encode($quiz->name),"Proctor")){
                            self::inject_password($PAGE, $quiz);
                            return;
                        }
                    }
                    break;
        }      
        // Logic for enabling proview for course level and quiz level ends


        if($COURSE && $COURSE->id) {
            //Logic for enabling specific user to use proctored assessment STARTS
            $group_details = $DB->get_record('groups', ['courseid' => $COURSE->id, 'name' => 'proview_disabled']); //fetching the group details for the proview_disabled group.

            if( !empty($group_details) ) {

                $group_member = $DB->get_record('groups_members', ['groupid' => $group_details->id, 'userid'=> $USER->id]);//request to check blacklist.

                if($group_member) {
                    $cm = $PAGE->cm;
                    $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
                    
                    self::inject_password($PAGE, $quiz);
                    return;
                }
            }
            //Logic for enabling specific user to use proctored assessment ENDS
            if (self::$injected) {
                return;
            }
            self::$injected = true;
        }

        $t = new api\tracker();
        $t::insert_tracking();
        return ;
    }

    /**
     * Toggle the state back to un-injected.
     *
     * @return null
     */
    public static function reset() {
        self::$injected = false;
        return ;
    }
}
