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
    public static function inject() {
        global $USER, $COURSE, $DB;
        $is_proctored = true;
        $group_list = $USER->groupmember[$COURSE->id]; //fetching the group for the current course.

        //Logic for enabling specific user to use proctered assessment STARTS
        if( !empty($group_list) ) {
            foreach ($group_list as $group) {
                $group_members = $DB->get_record('groups_members', ['id' => $group]);//request to get groupmembership details.
                
                $group_details = $DB->get_record('groups', ['id' => $group_members->groupid]);// request to get group details.

                if($group_details->name === 'proview_disabled') {
                    $is_proctored =false;
                }
            }
            if(!$is_proctored) {
                return;
            } 
        }
        //Logic for enabling specific user to use proctered assessment ENDS
        if (self::$injected) {
            return;
        }
        self::$injected = true;


        $enabled = get_config('local_proview', 'enabled');
        if (!$enabled) {
            return;
        }
        $t = new api\tracker();
        $t::insert_tracking();
    }

    /**
     * Toggle the state back to un-injected.
     *
     * @return null
     */
    public static function reset() {
        self::$injected = false;
    }
}
