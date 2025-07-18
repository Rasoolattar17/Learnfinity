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
 * Handle AJAX save requests for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Security checks
require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

// Only allow AJAX calls
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = new stdClass();
    
    try {
        $client_id = required_param('client_id', PARAM_TEXT);
        $client_secret = required_param('client_secret', PARAM_TEXT);
        $resource_id = required_param('resource_id', PARAM_TEXT);
        $selected_courses = optional_param('selected_courses', '', PARAM_TEXT);
        $integration_mode = optional_param('integration_mode', 'any', PARAM_ALPHA);
        
        // Save configuration
        set_config('client_id', $client_id, 'local_vanta');
        set_config('client_secret', $client_secret, 'local_vanta');
        set_config('resource_id', $resource_id, 'local_vanta');
        set_config('selected_courses', $selected_courses, 'local_vanta');
        set_config('integration_mode', $integration_mode, 'local_vanta');
        
        // Clear token cache to force regeneration with new credentials
        $cache = cache::make('local_vanta', 'token');
        $cache->delete('access_token');
        
        $response->status = 'success';
        $response->message = get_string('settingssaved', 'local_vanta');
    } catch (Exception $e) {
        $response->status = 'error';
        $response->message = $e->getMessage();
    }
    
    echo json_encode($response);
    die;
} else {
    throw new moodle_exception('noajax');
} 