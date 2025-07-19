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
 * Upgrade script for the test plugin.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for the test plugin.
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool Success status
 */
function xmldb_local_test_plugin_upgrade(int $oldversion): bool {
    global $CFG, $DB;

    $result = true;

    if ($oldversion < 2024120100) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2024120100, 'local', 'test_plugin');
    }

    return $result;
} 