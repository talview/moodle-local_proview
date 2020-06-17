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

namespace local_proview\api;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Guniversal analytics class.
 * @copyright  Talview, 2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tracker {
    /**
     * Insert the actual tracking code.
     *
     * @return void As the insertion is done through the {js} template API.
     */
    public static function insert_tracking() {
        global $PAGE, $OUTPUT, $USER;
        // var_dump($PAGE->url->path,"/moodle/mod/scorm/player.php");
        // var_dump($PAGE->url->params->scoid=="2");
        // die();
//         $class_name = get_class($PAGE->url);
// $methods = get_class_methods($class_name);
// foreach($methods as $method)
// {
//     var_dump($method);
//     echo "<br>";
// }
        // var_dump($PAGE->url);
        // var_dump($PAGE->url->get_param('scoid'));
        $pageinfo = get_context_info_array($PAGE->context->id);
        $template = new stdClass();
        var_dump($PAGE->requires->js_init_callback);
        $template->proview_url = get_config('local_proview', 'proview_url');
        $template->token = get_config('local_proview', 'token');
        $template->enabled = get_config('local_proview', 'enabled');
        $template->root_dir = get_config('local_proview', 'root_dir');
        $template->profile_id = $USER->id;
        $template->scorm_scoid=$PAGE->url->get_param('scoid');
        $template->scorm_cm=$PAGE->url->get_param('cm');
        $template->scorm_mode=$PAGE->url->get_param('mode');
        $template->scorm_currentorg=$PAGE->url->get_param('currentorg');
        $filecontent=file_get_contents ('stop_proview.txt');
        $template->disable_proview=false;
    //  if($filecontent)
    //  {
    //         var_dump($filecontent);
    //         var_dump($filecontent=='STOP PROVIEW');
    //         if($filecontent=='STOP PROVIEW'){
    //             $template->disable_proview=true;
    //             unlink('stop_proview.txt');
    //         }
    //  }
        // $template->scorm_cm
        // $template->checkUrl=$PAGE->url->path;
        // $template->scorm_params=$PAGE->url->params;
        // if ($PAGE->url->get_path()=="/moodle/mod/scorm/player.php") {
        //     $template->is_scorm= true;
        // }
        // else{
        //     $template->is_scorm= false;
        // }
        if ($pageinfo && !empty($template->token)) {
            // The templates only contains a "{js}" block; so we don't care about
            // the output; only that the $PAGE->requires are filled.
            // var_dump($template);
            $OUTPUT->render_from_template('local_proview/tracker', $template);
        }
    }
}
