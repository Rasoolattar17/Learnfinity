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
 * Library functions for local_testplugin
 *
 * @package    local_testplugin
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Library functions - no direct access check needed as this file only contains function definitions.

/**
 * Get plugin welcome message
 *
 * @return string Welcome message
 */
function local_testplugin_get_welcome_message() {
    return get_string('welcome', 'local_testplugin');
}

/**
 * Check if plugin is enabled
 *
 * @return bool True if enabled
 */
function local_testplugin_is_enabled() {
    return get_config('local_testplugin', 'enabled');
}

/**
 * Initialize plugin
 *
 * @return void
 */
function local_testplugin_init() {
    // Plugin initialization code here.
    if (local_testplugin_is_enabled()) {
        // Set default configuration values if not already set.
        if (get_config('local_testplugin', 'initialized') === false) {
            set_config('initialized', true, 'local_testplugin');
        }
    }
}
