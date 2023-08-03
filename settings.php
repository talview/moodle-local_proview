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
 * Proview Client
 *
 * This module provides support for remote proctoring quizzes and assessments using Proview
 *
 * @package    local_proview
 * @copyright  Talview 2020
 * @author     Talview Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @uses       die
 */

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {
    global $USER;

    $settings = new admin_settingpage('local_proview', get_string('pluginname', 'local_proview'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_proview/enabled';
    $title = get_string('enabled', 'local_proview');
    $description = get_string('enabled_desc', 'local_proview');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_proview/auto_password_injection_enabled';
    $title = get_string('auto_password_injection_enabled', 'local_proview');
    $description = get_string('auto_password_injection_enabled_desc', 'local_proview');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_proview/string_match';
    $title = get_string('string_match', 'local_proview');
    $description = get_string('string_match_desc', 'local_proview');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_proview/token';
    $title = get_string('token', 'local_proview');
    $description = get_string('token_desc', 'local_proview');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_proview/proview_url';
    $title = get_string('proview_url', 'local_proview');
    $description = get_string('proview_url_desc', 'local_proview');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_proview/proview_playback_url';
    $title = get_string('proview_playback_url', 'local_proview');
    $description = get_string('proview_playback_url_desc', 'local_proview');
    $default = 'https://appv7.proview.io/embedded';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_proview/proview_acc_name';
    $title = get_string('proview_acc_name', 'local_proview');
    $description = get_string('proview_acc_name_desc', 'local_proview');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_proview/root_dir';
    $title = get_string('root_dir', 'local_proview');
    $description = get_string('root_dir_desc', 'local_proview');
    $default = '/';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
}
