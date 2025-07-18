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
 * Vanta Integration Log Details View
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tabobject;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->libdir . '/weblib.php'); // For tabobject class
require_once($CFG->dirroot . '/composer/jwtsecure.php');
defined('MOODLE_INTERNAL') || die();
// Require login and verify capabilities
require_login();
$context = context_system::instance();
// require_capability('local/vanta:manage', $context);

// Get log ID from parameters
$id = required_param('id', PARAM_RAW);
$decoded = jwtsecure::decode($id);
$id = isset($decoded->id) ? (int)$decoded->id : 0;

if (empty($id)) {
    throw new moodle_exception('invalidlogid', 'local_vanta');
}

// Get log data
$log = $DB->get_record('local_vanta_sync_logs', ['id' => $id], '*', MUST_EXIST);

// Set up the page
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/vanta/view_log.php', ['id' => $id]));
$PAGE->set_pagelayout('administrator');
$PAGE->set_title(get_string('pluginname', 'local_vanta') . ': ' . get_string('log_details', 'local_vanta'));
$PAGE->set_heading(get_string('pluginname', 'local_vanta') . ': ' . get_string('log_details', 'local_vanta'));

// Add custom CSS for syntax highlighting
$PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css'));

// Navigation tabs
$tabs = [
    new tabobject('settings', new moodle_url('/local/vanta/index.php'), get_string('settings', 'local_vanta')),
    new tabobject('training_sync_rules', new moodle_url('/local/vanta/training_sync_rules.php'), get_string('training_sync_rules', 'local_vanta')),
    new tabobject('logs', new moodle_url('/local/vanta/logs.php'), get_string('logs', 'local_vanta'))
];

// Add navigation
$PAGE->navbar->add(get_string('pluginname', 'local_vanta'), new moodle_url('/local/vanta/index.php'));
$PAGE->navbar->add(get_string('logs', 'local_vanta'), new moodle_url('/local/vanta/logs.php'));
$PAGE->navbar->add(get_string('log_details', 'local_vanta'));

// Pretty-print JSON data
function format_json($json) {
    if (empty($json)) {
        return '';
    }
    
    $data = json_decode($json, true);
    if ($data) {
        // Replace sensitive information with user-friendly alternatives
        $data = sanitize_json_data($data);
        // If valid JSON, pretty print it
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        // If not valid JSON, return as is
        return $json;
    }
}

// Function to replace sensitive information in JSON data
function sanitize_json_data($data) {
    global $DB;
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = sanitize_json_data($value);
            } else if (is_string($value) || is_numeric($value)) {
                // Replace sensitive IDs with user-friendly names
                switch (strtolower($key)) {
                    case 'userid':
                    case 'user_id':
                        if (is_numeric($value) && $value > 0) {
                            $user = $DB->get_record('user', ['id' => $value], 'username, firstname, lastname');
                            if ($user) {
                                $data[$key . '_replaced'] = $user->firstname . ' ' . $user->lastname . ' (' . $user->username . ')';
                                $data[$key] = '[REPLACED_WITH_USERNAME]';
                            }
                        }
                        break;
                    case 'courseid':
                    case 'course_id':
                        if (is_numeric($value) && $value > 0) {
                            $course = $DB->get_record('course', ['id' => $value], 'fullname, shortname');
                            if ($course) {
                                $data[$key . '_replaced'] = $course->fullname . ' (' . $course->shortname . ')';
                                $data[$key] = '[REPLACED_WITH_COURSENAME]';
                            }
                        }
                        break;
                    case 'companyid':
                    case 'company_id':
                        if (is_numeric($value) && $value > 0) {
                            // If there's a company table, replace with company name
                            if ($DB->get_manager()->table_exists('company')) {
                                $company = $DB->get_record('company', ['id' => $value], 'name');
                                if ($company) {
                                    $data[$key . '_replaced'] = $company->name;
                                    $data[$key] = '[REPLACED_WITH_COMPANYNAME]';
                                }
                            }
                        }
                        break;
                }
            }
        }
    }
    
    return $data;
}

// Format request and response data
$request_payload = format_json($log->request_payload);
$response_payload = format_json($log->response_payload);

// Prepare template data
$data = [
    'log' => [
        'id' => $log->id,
        'useremail' => $log->useremail,
        'coursename' => $log->coursename,
        'syncedon' => $log->syncedon,
        'syncedon_formatted' => userdate($log->syncedon, get_string('strftimedatetime', 'langconfig')),
        'status' => $log->status,
        'status_class' => $log->status === 'success' ? 'badge-success' : 'badge-danger',
        'has_error' => !empty($log->error_message),
        'error_message' => $log->error_message,
        'request_payload' => $log->request_payload,
        'formatted_request' => $request_payload,
        'response_payload' => $log->response_payload,
        'formatted_response' => $response_payload
    ],
    'logs_url' => new moodle_url('/local/vanta/logs.php')
];
// var_dump($data);die;
// Display the page
echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, 'logs');
echo $OUTPUT->render_from_template('local_vanta/log_details', $data);

// Add syntax highlighting for JSON
echo '
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/languages/json.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll("pre").forEach(block => {
            hljs.highlightElement(block);
        });
    });
</script>
';

echo $OUTPUT->footer(); 