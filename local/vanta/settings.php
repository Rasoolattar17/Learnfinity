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
 * Settings for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a new admin category for plugins related to compliance
    $ADMIN->add('localplugins', new admin_category('vanta',
        get_string('pluginname', 'local_vanta')));
        
    // Create the main settings page
    $ADMIN->add('vanta', new admin_externalpage(
        'local_vanta',
        get_string('settings', 'local_vanta'),
        $CFG->wwwroot . '/local/vanta/index.php'
    ));
    
    // Add logs page
    $ADMIN->add('vanta', new admin_externalpage(
        'local_vanta_logs',
        get_string('logs', 'local_vanta'),
        $CFG->wwwroot . '/local/vanta/logs.php'
    ));
} 