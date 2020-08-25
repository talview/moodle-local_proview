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

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade local_proview.
 *
 * @param int $oldversion
 * @return bool always true
 */

function xmldb_local_proview_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2020031901 ) {
        $options = array(
            'enabled'       => true,
            'token'         => '',
            'proview_url'   => '//cdn.proview.io/init.js',
            'root_dir'      => '/'
        );
        foreach ($options as $key => $value ) {
            $new = new stdClass();
            $new->plugin = 'local_proview';
            $new->name = $key;
            $new->value = $value;
            $DB->insert_record('config_plugins', $new);
        }

        upgrade_plugin_savepoint(true, 2020031901, 'local', 'proview');
    }
    
    if ($oldversion < 2020082401) {

        // Define table local_proview to be created.
        $table = new xmldb_table('local_proview');

        // Adding fields to table local_proview.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quiz_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('proview_url', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attempt_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_proview.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_proview.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Proview savepoint reached.
        upgrade_plugin_savepoint(true, 2020082401, 'local', 'proview');
    }

    return true;
}
