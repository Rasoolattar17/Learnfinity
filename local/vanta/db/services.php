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
 * Web service definitions for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_vanta_save_credentials' => [
        'classname'   => 'local_vanta_external',
        'methodname'  => 'save_credentials',
        'classpath'   => 'local/vanta/externallib.php',
        'description' => 'Save Vanta API credentials',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_vanta_update_credentials' => [
        'classname'   => 'local_vanta_external',
        'methodname'  => 'update_credentials',
        'classpath'   => 'local/vanta/externallib.php',
        'description' => 'Update existing Vanta API credentials',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_vanta_save_sync_rules' => [
        'classname'   => 'local_vanta_external',
        'methodname'  => 'save_sync_rules',
        'classpath'   => 'local/vanta/externallib.php',
        'description' => 'Save Vanta completion sync rules',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_vanta_get_sync_rules' => [
        'classname'   => 'local_vanta_external',
        'methodname'  => 'get_sync_rules',
        'classpath'   => 'local/vanta/externallib.php',
        'description' => 'Get Vanta completion sync rules',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'local_vanta_get_logs' => [
        'classname'     => 'local_vanta_external',
        'methodname'    => 'get_logs',
        'classpath'     => 'local/vanta/externallib.php',
        'description'   => 'Get filtered Vanta logs',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/vanta:viewlogs'
    ],
    'local_vanta_get_course_options' => [
        'classname'     => 'local_vanta_external',
        'methodname'    => 'get_course_options',
        'classpath'     => 'local/vanta/externallib.php',
        'description'   => 'Get course options for logs filter',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/vanta:viewlogs'
    ]
];

$services = [
    'Vanta Integration Services' => [
        'functions' => [
            'local_vanta_save_credentials',
            'local_vanta_update_credentials',
            'local_vanta_save_sync_rules',
            'local_vanta_get_sync_rules'
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ]
]; 