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

        $enabled = get_config('local_proview', 'enabled');
        if (!$enabled) {
            return;
        }
        if($COURSE && $COURSE->id) {
            //Logic for enabling specific user to use proctored assessment STARTS
            $group_details = $DB->get_record('groups', ['courseid' => $COURSE->id, 'name' => 'proview_disabled']); //fetching the group details for the proview_disabled group.

            if( !empty($group_details) ) {

                $group_member = $DB->get_record('groups_members', ['groupid' => $group_details->id, 'userid'=> $USER->id]);//request to check blacklist.

                if($group_member) {
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
