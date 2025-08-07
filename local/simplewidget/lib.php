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
 * Library functions for local_simplewidget
 *
 * @package    local_simplewidget
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Library functions - no direct access check needed as this file only contains function definitions.

/**
 * Get current user information
 *
 * @return array User information
 */
function local_simplewidget_get_user_info() {
    global $USER;

    return [
        'id' => $USER->id,
        'fullname' => fullname($USER),
        'email' => $USER->email,
        'lastaccess' => userdate($USER->lastaccess),
    ];
}

/**
 * Get site statistics
 *
 * @return array Site statistics
 */
function local_simplewidget_get_site_stats() {
    global $DB;

    $stats = [];

    // Get course count.
    $stats['courses'] = $DB->count_records('course', ['visible' => 1]);

    // Get user count.
    $stats['users'] = $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]);

    // Get current time.
    $stats['current_time'] = date('Y-m-d H:i:s');

    return $stats;
}

/**
 * Check if user can view the widget
 *
 * @return bool True if user can view
 */
function local_simplewidget_can_view() {
    global $USER;

    // Allow site administrators and teachers.
    return has_capability('moodle/site:config', context_system::instance()) ||
           has_capability('moodle/course:manageactivities', context_system::instance());
}
