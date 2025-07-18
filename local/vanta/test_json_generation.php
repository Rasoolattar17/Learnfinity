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
 * Test page for Vanta JSON generation
 *
 * @package    local_vanta
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');
require_once($CFG->dirroot . '/local/vanta/classes/rule_manager.php');

use core\context\system as context_system;
use moodle_url;

// Security checks
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
// Page setup
$PAGE->set_url(new \moodle_url('/local/vanta/test_json_generation.php'));
$PAGE->set_context($context);
$PAGE->set_title('Vanta JSON Generation Test');
$PAGE->set_heading('Vanta JSON Generation Test');
$PAGE->set_pagelayout('admin');

// Get parameters
$action = optional_param('action', '', PARAM_ALPHA);
$company_id = optional_param('company_id', 0, PARAM_INT);

// Get company ID from session if not provided
if (!$company_id && isset($_SESSION['USER']) && isset($_SESSION['USER']->companyid)) {
    $company_id = $_SESSION['USER']->companyid;
}

echo $OUTPUT->header();

echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="col-12">';

echo '<div class="alert alert-info">';
echo '<h4>Vanta JSON Generation Test</h4>';
echo '<p>This page demonstrates how the Vanta integration system generates JSON files for completion data.</p>';
echo '</div>';

// Show current company info
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Company Information</h5></div>';
echo '<div class="card-body">';
echo '<p><strong>Company ID:</strong> ' . $company_id . '</p>';

// Get Vanta credentials
$vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
if ($vanta_id) {
    echo '<p><strong>Vanta ID:</strong> ' . $vanta_id . '</p>';
    
    // Get sync rule
    $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]);
    if ($sync_rule) {
        echo '<p><strong>Sync Rule Found:</strong> Yes</p>';
        echo '<p><strong>Courses:</strong> ' . ($sync_rule->courses ?: 'None') . '</p>';
        echo '<p><strong>Completion Mode:</strong> ' . $sync_rule->completion_mode . '</p>';
    } else {
        echo '<p><strong>Sync Rule Found:</strong> No</p>';
    }
} else {
    echo '<p><strong>Vanta Credentials:</strong> Not found</p>';
}
echo '</div>';
echo '</div>';

// Action buttons
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Actions</h5></div>';
echo '<div class="card-body">';

$regenerate_url = new moodle_url('/local/vanta/test_json_generation.php', [
    'action' => 'regenerate',
    'company_id' => $company_id,
    'sesskey' => sesskey()
]);

$view_data_url = new moodle_url('/local/vanta/test_json_generation.php', [
    'action' => 'view_data',
    'company_id' => $company_id
]);

$check_lock_url = new moodle_url('/local/vanta/test_json_generation.php', [
    'action' => 'check_lock',
    'company_id' => $company_id
]);

echo '<a href="' . $regenerate_url . '" class="btn btn-primary mr-2">Regenerate JSON Data</a>';
echo '<a href="' . $view_data_url . '" class="btn btn-info mr-2">View Current Data</a>';
echo '<a href="' . $check_lock_url . '" class="btn btn-secondary mr-2">Check Lock Status</a>';

echo '</div>';
echo '</div>';

// Process actions
if ($action && confirm_sesskey()) {
    echo '<div class="card mb-3">';
    echo '<div class="card-header"><h5>Action Results</h5></div>';
    echo '<div class="card-body">';
    
    switch ($action) {
        case 'regenerate':
            echo '<h6>Regenerating JSON Data...</h6>';
            
            try {
                $success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
                
                if ($success) {
                    echo '<div class="alert alert-success">✓ JSON data regenerated successfully!</div>';
                    
                    // Show file location
                    $data_file = $CFG->dataroot . '/local_vanta/vanta_data_' . $company_id . '.json';
                    echo '<p><strong>File Location:</strong> ' . $data_file . '</p>';
                    
                    if (file_exists($data_file)) {
                        $file_size = filesize($data_file);
                        echo '<p><strong>File Size:</strong> ' . $file_size . ' bytes</p>';
                        echo '<p><strong>Last Modified:</strong> ' . date('Y-m-d H:i:s', filemtime($data_file)) . '</p>';
                    }
                } else {
                    echo '<div class="alert alert-danger">✗ Failed to regenerate JSON data</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">✗ Error: ' . $e->getMessage() . '</div>';
            }
            break;
            
        case 'view_data':
            echo '<h6>Current JSON Data:</h6>';
            
            $data = \local_vanta\sync_manager::load_completion_data($company_id);
            
            if ($data) {
                echo '<div class="alert alert-success">Data file found!</div>';
                echo '<p><strong>Generated At:</strong> ' . date('Y-m-d H:i:s', $data['generated_at']) . '</p>';
                echo '<p><strong>Record Count:</strong> ' . $data['completion_count'] . '</p>';
                
                echo '<h6>Sample Data (first 3 records):</h6>';
                echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;">';
                
                $sample_data = array_slice($data['data'], 0, 3);
                echo htmlspecialchars(json_encode($sample_data, JSON_PRETTY_PRINT));
                
                echo '</pre>';
                
                if (count($data['data']) > 3) {
                    echo '<p><em>... and ' . (count($data['data']) - 3) . ' more records</em></p>';
                }
            } else {
                echo '<div class="alert alert-warning">No JSON data file found. Try regenerating the data first.</div>';
            }
            break;
            
        case 'check_lock':
            echo '<h6>Lock Status:</h6>';
            
            $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
            
            if ($locked) {
                echo '<div class="alert alert-warning">🔒 Sync is currently LOCKED</div>';
                
                // Try to get lock details
                $lock_file = $CFG->dataroot . '/local_vanta/vanta_sync_lock_' . $company_id . '.lock';
                if (file_exists($lock_file)) {
                    $lock_data = json_decode(file_get_contents($lock_file), true);
                    if ($lock_data) {
                        echo '<p><strong>Lock Details:</strong></p>';
                        echo '<ul>';
                        echo '<li>Operation: ' . ($lock_data['operation'] ?? 'unknown') . '</li>';
                        echo '<li>User ID: ' . ($lock_data['user_id'] ?? 'unknown') . '</li>';
                        echo '<li>Timestamp: ' . date('Y-m-d H:i:s', $lock_data['timestamp'] ?? 0) . '</li>';
                        echo '<li>PID: ' . ($lock_data['pid'] ?? 'unknown') . '</li>';
                        echo '</ul>';
                    }
                }
            } else {
                echo '<div class="alert alert-success">🔓 Sync is UNLOCKED</div>';
            }
            
            // Queue stats
            $queue_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
            echo '<h6>Queue Statistics:</h6>';
            echo '<ul>';
            echo '<li>Pending: ' . $queue_stats['pending'] . '</li>';
            echo '<li>Completed: ' . $queue_stats['completed'] . '</li>';
            echo '<li>Failed: ' . $queue_stats['failed'] . '</li>';
            echo '</ul>';
            
            // Regeneration status
            $regen_status = \local_vanta\rule_manager::get_regeneration_status($company_id);
            if ($regen_status) {
                echo '<h6>Regeneration Status:</h6>';
                echo '<p><strong>Status:</strong> ' . $regen_status['status'] . '</p>';
                if ($regen_status['queued_at']) {
                    echo '<p><strong>Queued:</strong> ' . date('Y-m-d H:i:s', $regen_status['queued_at']) . '</p>';
                }
            }
            break;
    }
    
    echo '</div>';
    echo '</div>';
}

// Show file system info
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>File System Information</h5></div>';
echo '<div class="card-body">';

$vanta_dir = $CFG->dataroot . '/local_vanta';
echo '<p><strong>Vanta Data Directory:</strong> ' . $vanta_dir . '</p>';

if (is_dir($vanta_dir)) {
    echo '<p><strong>Directory Exists:</strong> Yes</p>';
    
    $files = glob($vanta_dir . '/*');
    if ($files) {
        echo '<p><strong>Files in directory:</strong></p>';
        echo '<ul>';
        foreach ($files as $file) {
            $filename = basename($file);
            $size = is_file($file) ? filesize($file) : 0;
            $modified = filemtime($file);
            echo '<li>' . $filename . ' (' . $size . ' bytes, modified: ' . date('Y-m-d H:i:s', $modified) . ')</li>';
        }
        echo '</ul>';
    } else {
        echo '<p><strong>Files:</strong> None found</p>';
    }
} else {
    echo '<p><strong>Directory Exists:</strong> No (will be created when needed)</p>';
}

echo '</div>';
echo '</div>';

echo '</div>'; // col-12
echo '</div>'; // row
echo '</div>'; // container-fluid

echo $OUTPUT->footer(); 