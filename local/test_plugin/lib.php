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
 * Library of functions for the test plugin.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extends the global navigation tree by adding test plugin nodes if there is a capability.
 *
 * @param \global_navigation $navigation An object representing the navigation tree
 * @return void
 */
function local_test_plugin_extend_navigation(\global_navigation $navigation): void {
    global $PAGE, $USER;

    if (has_capability('local/test_plugin:view', \context_system::instance())) {
        $testnode = \navigation_node::create(
            get_string('pluginname', 'local_test_plugin'),
            new \moodle_url('/local/test_plugin/index.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'testplugin',
            new \pix_icon('t/help', '')
        );
        $navigation->add_node($testnode);
    }
}

/**
 * Extends the settings navigation with the test plugin settings.
 *
 * @param \settings_navigation $settingsnav The settings navigation object
 * @param \navigation_node $testpluginnode The test plugin node in the navigation tree
 * @return void
 */
function local_test_plugin_extend_settings_navigation(\settings_navigation $settingsnav, \navigation_node $testpluginnode): void {
    global $PAGE;

    if (has_capability('local/test_plugin:manage', \context_system::instance())) {
        $testpluginnode->add(
            get_string('settings', 'local_test_plugin'),
            new \moodle_url('/local/test_plugin/settings.php')
        );
    }
}

/**
 * Get the test data for the plugin.
 *
 * @param int $userid The user ID
 * @return array<string, mixed> The test data
 */
function local_test_plugin_get_test_data(int $userid): array {
    global $DB;

    $data = [
        'userid' => $userid,
        'timestamp' => time(),
        'status' => 'active',
    ];

    return $data;
}

/**
 * Process the test form data.
 *
 * @param \stdClass $data The form data
 * @return int|false The record ID or false on failure
 */
function local_test_plugin_process_form(\stdClass $data): int|false {
    global $DB, $USER;

    $record = new \stdClass();
    $record->userid = $USER->id;
    $record->name = $data->name;
    $record->description = $data->description;
    $record->timecreated = time();
    $record->timemodified = time();

    return $DB->insert_record('local_test_plugin_data', $record);
}