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
 * Hello World plugin library functions.
 *
 * @package    local_hello
 * @copyright  2025, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generate a greeting message.
 *
 * @param string $name The name to greet
 * @return string The greeting message
 */
function local_hello_generate_greeting($name = '') {
    if (empty($name)) {
        $name = get_string('world', 'local_hello');
    }
    return get_string('greeting', 'local_hello', $name);
}

/**
 * Get CI/CD test status.
 *
 * @return string The CI/CD test status message
 */
function local_hello_get_cicd_status() {
    return get_string('ci_cd_test', 'local_hello');
}

/**
 * Get the plugin's configuration settings.
 *
 * @return stdClass The configuration object
 */
function local_hello_get_config() {
    return get_config('local_hello');
}

/**
 * Set a configuration value for the plugin.
 *
 * @param string $name The configuration name
 * @param string $value The configuration value
 * @return bool True on success
 */
function local_hello_set_config($name, $value) {
    return set_config($name, $value, 'local_hello');
}
