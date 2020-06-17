<?php
namespace local_proview;
use local_proview\injector;

defined('MOODLE_INTERNAL') || die();

class observer
{

    public static function stop(\mod_scorm\event\status_submitted $event)
    {
        file_put_contents('stop_proview.txt', "STOP PROVIEW");
        var_dump($event);
        // echo '<script Xlanguage="JavaScript" type="text/JavaScript">console.log("STOP CONSOLE LOG MESSAGE");</script>'; 
        echo "<script>parent.postMessage({type: 'stopProview',url: window.location.href}, childOrigin);</script>";

    }


    public static function launched(\mod_scorm\event\sco_launched $event)
    {
        file_put_contents('launched.txt', "LAUNCH DATA");
        var_dump($event);
        echo '<script>console.log("LAUNCH");</script>';
    }

    public static function score(\mod_scorm\event\scoreraw_submitted $event)
    {
        // require_once('../../../config.php');
        // global $OUTPUT,$PAGE;
        file_put_contents('score.txt', "Launch ");
        // $PAGE->set_context(context_system::instance());

        // $PAGE->requires->js('/local/proview/requirejs_init.js');
        // $PAGE->requires->js_init_call('hello');
        // echo '<script Xlanguage="JavaScript" type="text/JavaScript">console.log("Score CONSOLE LOG MESSAGE");</script>'; 
        // var_dump($PAGE);
    }

}