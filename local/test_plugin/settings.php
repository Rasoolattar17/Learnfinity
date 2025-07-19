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
 * Settings page for the test plugin.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

$ADMIN->add('localplugins', new admin_category('testplugin', get_string('pluginname', 'local_test_plugin')));

$settings = new admin_settingpage('local_test_plugin', get_string('settings', 'local_test_plugin'));
$ADMIN->add('testplugin', $settings);

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('local_test_plugin/heading',
        get_string('settings_heading', 'local_test_plugin'),
        get_string('settings_heading_desc', 'local_test_plugin')
    ));

    $settings->add(new admin_setting_configcheckbox('local_test_plugin/enabled',
        get_string('setting_enabled', 'local_test_plugin'),
        get_string('setting_enabled_desc', 'local_test_plugin'),
        1
    ));
} 