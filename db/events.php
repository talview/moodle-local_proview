<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\mod_scorm\event\status_submitted',
        'callback' => 'local_proview\observer::stop',
    ),
    array(
        'eventname' => '\mod_scorm\event\sco_launched',
        'callback' => 'local_proview\observer::launched',
    ),
    array(
        'eventname' => '\mod_scorm\event\scoreraw_submitted',
        'callback' => 'local_proview\observer::score',
    ),

);
