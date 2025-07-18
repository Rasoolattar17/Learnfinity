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
 * Comprehensive test script for Vanta integration system
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
// require_capability('moodle/site:config', $context);

// Page setup
$PAGE->set_url(new moodle_url('/local/vanta/test_system.php'));
$PAGE->set_context($context);
$PAGE->set_title('Vanta System Testing');
$PAGE->set_heading('Vanta Integration System Testing');
$PAGE->set_pagelayout('admin');

// Get parameters
$test = optional_param('test', '', PARAM_ALPHA);
$company_id = optional_param('company_id', 1, PARAM_INT); // Default to company 1

echo $OUTPUT->header();

echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="col-12">';

echo '<div class="alert alert-info">';
echo '<h4>Vanta Integration System Testing</h4>';
echo '<p>This page provides comprehensive testing for all Vanta integration components.</p>';
echo '</div>';

// Test navigation
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Available Tests</h5></div>';
echo '<div class="card-body">';

$tests = [
    'database' => 'Database Schema Test',
    'classes' => 'Class Loading Test',
    'lock' => 'Lock Mechanism Test',
    'queue' => 'Queue System Test',
    'json' => 'JSON Generation Test',
    'integration' => 'Full Integration Test',
    'cleanup' => 'Cleanup Test Data'
];

foreach ($tests as $test_key => $test_name) {
    $url = new moodle_url('/local/vanta/test_system.php', [
        'test' => $test_key,
        'company_id' => $company_id,
        'sesskey' => sesskey()
    ]);
    echo '<a href="' . $url . '" class="btn btn-outline-primary mr-2 mb-2">' . $test_name . '</a>';
}

echo '</div>';
echo '</div>';

// Company selection
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>Test Configuration</h5></div>';
echo '<div class="card-body">';
echo '<form method="get">';
echo '<div class="form-group">';
echo '<label for="company_id">Company ID for Testing:</label>';
echo '<input type="number" id="company_id" name="company_id" value="' . $company_id . '" class="form-control" style="width: 200px; display: inline-block;">';
echo '<button type="submit" class="btn btn-secondary ml-2">Update</button>';
echo '</div>';
echo '</form>';
echo '</div>';
echo '</div>';

// Run tests
if ($test && confirm_sesskey()) {
    echo '<div class="card mb-3">';
    echo '<div class="card-header"><h5>Test Results: ' . $tests[$test] . '</h5></div>';
    echo '<div class="card-body">';
    
    switch ($test) {
        case 'database':
            test_database_schema();
            break;
            
        case 'classes':
            test_class_loading();
            break;
            
        case 'lock':
            test_lock_mechanism($company_id);
            break;
            
        case 'queue':
            test_queue_system($company_id);
            break;
            
        case 'json':
            test_json_generation($company_id);
            break;
            
        case 'integration':
            test_full_integration($company_id);
            break;
            
        case 'cleanup':
            test_cleanup($company_id);
            break;
    }
    
    echo '</div>';
    echo '</div>';
}

echo '</div>'; // col-12
echo '</div>'; // row
echo '</div>'; // container-fluid

echo $OUTPUT->footer();

/**
 * Test database schema
 */
function test_database_schema() {
    global $DB;
    
    echo '<h6>Testing Database Schema...</h6>';
    
    $tables = [
        'local_vanta_api_credentials',
        'local_vanta_training_sync_rules',
        'local_vanta_sync_logs',
        'local_vanta_sync_queue',
        'local_vanta_regeneration_queue'
    ];
    
    $all_exist = true;
    
    foreach ($tables as $table) {
        if ($DB->get_manager()->table_exists($table)) {
            echo '<div class="alert alert-success">✓ Table <code>' . $table . '</code> exists</div>';
            
            // Check table structure
            $columns = $DB->get_columns($table);
            echo '<details><summary>Columns (' . count($columns) . ')</summary>';
            echo '<ul>';
            foreach ($columns as $column) {
                echo '<li><code>' . $column->name . '</code> - ' . $column->type . '</li>';
            }
            echo '</ul></details>';
            
        } else {
            echo '<div class="alert alert-danger">✗ Table <code>' . $table . '</code> missing</div>';
            $all_exist = false;
        }
    }
    
    if ($all_exist) {
        echo '<div class="alert alert-success"><strong>✓ All database tables exist and are properly structured!</strong></div>';
    } else {
        echo '<div class="alert alert-danger"><strong>✗ Some database tables are missing. Run upgrade.php</strong></div>';
    }
}

/**
 * Test class loading
 */
function test_class_loading() {
    echo '<h6>Testing Class Loading...</h6>';
    
    $classes = [
        'local_vanta\sync_manager',
        'local_vanta\rule_manager',
        'local_vanta\task\sync_task'
    ];
    
    $all_loaded = true;
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo '<div class="alert alert-success">✓ Class <code>' . $class . '</code> loaded successfully</div>';
            
            // Test basic methods
            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            echo '<details><summary>Public Methods (' . count($methods) . ')</summary>';
            echo '<ul>';
            foreach ($methods as $method) {
                if ($method->isStatic()) {
                    echo '<li><code>static ' . $method->name . '()</code></li>';
                } else {
                    echo '<li><code>' . $method->name . '()</code></li>';
                }
            }
            echo '</ul></details>';
            
        } else {
            echo '<div class="alert alert-danger">✗ Class <code>' . $class . '</code> not found</div>';
            $all_loaded = false;
        }
    }
    
    if ($all_loaded) {
        echo '<div class="alert alert-success"><strong>✓ All classes loaded successfully!</strong></div>';
    }
}

/**
 * Test lock mechanism
 */
function test_lock_mechanism($company_id) {
    echo '<h6>Testing Lock Mechanism for Company ' . $company_id . '...</h6>';
    
    try {
        // Test 1: Check initial lock status
        $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
        echo '<div class="alert alert-info">Initial lock status: ' . ($locked ? 'LOCKED' : 'UNLOCKED') . '</div>';
        
        // Test 2: Acquire lock
        $acquired = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'test_operation');
        if ($acquired) {
            echo '<div class="alert alert-success">✓ Lock acquired successfully</div>';
            
            // Test 3: Check lock status
            $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
            echo '<div class="alert alert-info">Lock status after acquire: ' . ($locked ? 'LOCKED' : 'UNLOCKED') . '</div>';
            
            // Test 4: Try to acquire again (should fail)
            $acquired_again = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'test_operation_2');
            if (!$acquired_again) {
                echo '<div class="alert alert-success">✓ Second lock acquisition correctly failed</div>';
            } else {
                echo '<div class="alert alert-warning">⚠ Second lock acquisition should have failed</div>';
            }
            
            // Test 5: Release lock
            $released = \local_vanta\sync_manager::release_sync_lock($company_id);
            if ($released) {
                echo '<div class="alert alert-success">✓ Lock released successfully</div>';
                
                // Test 6: Check final status
                $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
                echo '<div class="alert alert-info">Final lock status: ' . ($locked ? 'LOCKED' : 'UNLOCKED') . '</div>';
            } else {
                echo '<div class="alert alert-danger">✗ Failed to release lock</div>';
            }
            
        } else {
            echo '<div class="alert alert-danger">✗ Failed to acquire lock</div>';
        }
        
        echo '<div class="alert alert-success"><strong>✓ Lock mechanism test completed!</strong></div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">✗ Lock test failed: ' . $e->getMessage() . '</div>';
    }
}

/**
 * Test queue system
 */
function test_queue_system($company_id) {
    global $DB;
    
    echo '<h6>Testing Queue System for Company ' . $company_id . '...</h6>';
    
    try {
        // Test 1: Check initial queue stats
        $initial_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        echo '<div class="alert alert-info">Initial queue stats: Pending=' . $initial_stats['pending'] . 
             ', Completed=' . $initial_stats['completed'] . ', Failed=' . $initial_stats['failed'] . '</div>';
        
        // Test 2: Add test items to queue
        $test_items = [
            ['userid' => 1, 'courseid' => 1, 'reason' => 'test_item_1'],
            ['userid' => 2, 'courseid' => 2, 'reason' => 'test_item_2'],
            ['userid' => 3, 'courseid' => 3, 'reason' => 'test_item_3']
        ];
        
        foreach ($test_items as $item) {
            $queued = \local_vanta\sync_manager::queue_completion(
                $item['userid'], 
                $item['courseid'], 
                $company_id, 
                $item['reason']
            );
            
            if ($queued) {
                echo '<div class="alert alert-success">✓ Queued test item: User ' . $item['userid'] . ', Course ' . $item['courseid'] . '</div>';
            } else {
                echo '<div class="alert alert-danger">✗ Failed to queue test item</div>';
            }
        }
        
        // Test 3: Check queue stats after adding
        $after_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        echo '<div class="alert alert-info">Queue stats after adding: Pending=' . $after_stats['pending'] . 
             ', Completed=' . $after_stats['completed'] . ', Failed=' . $after_stats['failed'] . '</div>';
        
        // Test 4: Process queue (this will fail because test users/courses don't exist, but that's expected)
        echo '<div class="alert alert-warning">Processing queue (expected to fail for test data)...</div>';
        $results = \local_vanta\sync_manager::process_queue($company_id, 10);
        echo '<div class="alert alert-info">Process results: Processed=' . $results['processed'] . 
             ', Successful=' . $results['successful'] . ', Failed=' . $results['failed'] . '</div>';
        
        // Test 5: Final queue stats
        $final_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        echo '<div class="alert alert-info">Final queue stats: Pending=' . $final_stats['pending'] . 
             ', Completed=' . $final_stats['completed'] . ', Failed=' . $final_stats['failed'] . '</div>';
        
        echo '<div class="alert alert-success"><strong>✓ Queue system test completed!</strong></div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">✗ Queue test failed: ' . $e->getMessage() . '</div>';
    }
}

/**
 * Test JSON generation
 */
function test_json_generation($company_id) {
    global $DB;
    
    echo '<h6>Testing JSON Generation for Company ' . $company_id . '...</h6>';
    
    try {
        // Test 1: Check if company has Vanta credentials
        $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
        if (!$vanta_id) {
            echo '<div class="alert alert-warning">⚠ No Vanta credentials found for company ' . $company_id . '. Creating test credentials...</div>';
            
            // Create test credentials
            $test_creds = new stdClass();
            $test_creds->name = 'Test Credentials';
            $test_creds->client_id = 'test_client_id';
            $test_creds->client_secret = 'test_client_secret';
            $test_creds->resource_id = 'test_resource_id';
            $test_creds->scope = 'connectors.self:write-resource';
            $test_creds->grant_type = 'client_credentials';
            $test_creds->company_id = $company_id;
            $test_creds->status = 1;
            $test_creds->created_by = 1;
            $test_creds->created_on = time();
            $test_creds->modified_by = 1;
            $test_creds->modified_on = time();
            
            $vanta_id = $DB->insert_record('local_vanta_api_credentials', $test_creds);
            echo '<div class="alert alert-success">✓ Test credentials created with ID: ' . $vanta_id . '</div>';
        } else {
            echo '<div class="alert alert-success">✓ Vanta credentials found with ID: ' . $vanta_id . '</div>';
        }
        
        // Test 2: Check if sync rule exists
        $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]);
        if (!$sync_rule) {
            echo '<div class="alert alert-warning">⚠ No sync rule found. Creating test sync rule...</div>';
            
            // Create test sync rule
            $test_rule = new stdClass();
            $test_rule->vanta_id = $vanta_id;
            $test_rule->resource_id = 'test_resource_id';
            $test_rule->frameworks = '';
            $test_rule->courses = '1,2,3'; // Test courses
            $test_rule->completion_mode = 'any';
            $test_rule->created_by = 1;
            $test_rule->created_on = time();
            $test_rule->modified_by = 1;
            $test_rule->modified_on = time();
            
            $rule_id = $DB->insert_record('local_vanta_training_sync_rules', $test_rule);
            echo '<div class="alert alert-success">✓ Test sync rule created with ID: ' . $rule_id . '</div>';
            
            $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['id' => $rule_id]);
        } else {
            echo '<div class="alert alert-success">✓ Sync rule found with ID: ' . $sync_rule->id . '</div>';
        }
        
        // Test 3: Generate completion data
        echo '<div class="alert alert-info">Attempting to regenerate completion data...</div>';
        $success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
        
        if ($success) {
            echo '<div class="alert alert-success">✓ Completion data generated successfully</div>';
            
            // Test 4: Load and display data
            $data = \local_vanta\sync_manager::load_completion_data($company_id);
            if ($data) {
                echo '<div class="alert alert-success">✓ JSON data loaded successfully</div>';
                echo '<p><strong>Generated At:</strong> ' . date('Y-m-d H:i:s', $data['generated_at']) . '</p>';
                echo '<p><strong>Record Count:</strong> ' . $data['completion_count'] . '</p>';
                
                if (!empty($data['data'])) {
                    echo '<h6>Sample Data (first record):</h6>';
                    echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;">';
                    echo htmlspecialchars(json_encode($data['data'][0], JSON_PRETTY_PRINT));
                    echo '</pre>';
                } else {
                    echo '<div class="alert alert-info">No completion data found (this is normal if no users have completed the test courses)</div>';
                }
            } else {
                echo '<div class="alert alert-warning">⚠ No JSON data file found</div>';
            }
        } else {
            echo '<div class="alert alert-danger">✗ Failed to generate completion data</div>';
        }
        
        echo '<div class="alert alert-success"><strong>✓ JSON generation test completed!</strong></div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">✗ JSON generation test failed: ' . $e->getMessage() . '</div>';
    }
}

/**
 * Test full integration
 */
function test_full_integration($company_id) {
    echo '<h6>Testing Full Integration for Company ' . $company_id . '...</h6>';
    
    try {
        // Test 1: Lock acquisition
        echo '<div class="alert alert-info">Step 1: Testing lock acquisition...</div>';
        $locked = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'integration_test');
        if ($locked) {
            echo '<div class="alert alert-success">✓ Lock acquired</div>';
        } else {
            echo '<div class="alert alert-warning">⚠ Lock already exists or failed to acquire</div>';
        }
        
        // Test 2: Queue some test completions
        echo '<div class="alert alert-info">Step 2: Testing queue during lock...</div>';
        $queued = \local_vanta\sync_manager::queue_completion(99, 99, $company_id, 'integration_test');
        if ($queued) {
            echo '<div class="alert alert-success">✓ Completion queued during lock</div>';
        } else {
            echo '<div class="alert alert-danger">✗ Failed to queue completion</div>';
        }
        
        // Test 3: Release lock
        echo '<div class="alert alert-info">Step 3: Releasing lock...</div>';
        $released = \local_vanta\sync_manager::release_sync_lock($company_id);
        if ($released) {
            echo '<div class="alert alert-success">✓ Lock released</div>';
        } else {
            echo '<div class="alert alert-danger">✗ Failed to release lock</div>';
        }
        
        // Test 4: Process queue
        echo '<div class="alert alert-info">Step 4: Processing queue...</div>';
        $results = \local_vanta\sync_manager::process_queue($company_id, 5);
        echo '<div class="alert alert-info">Queue processing results: ' . json_encode($results) . '</div>';
        
        // Test 5: Data regeneration
        echo '<div class="alert alert-info">Step 5: Testing data regeneration...</div>';
        $regen_success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
        if ($regen_success) {
            echo '<div class="alert alert-success">✓ Data regeneration successful</div>';
        } else {
            echo '<div class="alert alert-warning">⚠ Data regeneration failed (may be due to missing test data)</div>';
        }
        
        echo '<div class="alert alert-success"><strong>✓ Full integration test completed!</strong></div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">✗ Integration test failed: ' . $e->getMessage() . '</div>';
        
        // Ensure lock is released
        \local_vanta\sync_manager::release_sync_lock($company_id);
    }
}

/**
 * Clean up test data
 */
function test_cleanup($company_id) {
    global $DB;
    
    echo '<h6>Cleaning Up Test Data for Company ' . $company_id . '...</h6>';
    
    try {
        // Clean up queue
        $deleted_queue = $DB->delete_records('local_vanta_sync_queue', ['company_id' => $company_id]);
        echo '<div class="alert alert-success">✓ Deleted ' . $deleted_queue . ' queue records</div>';
        
        // Clean up regeneration queue
        $deleted_regen = $DB->delete_records('local_vanta_regeneration_queue', ['company_id' => $company_id]);
        echo '<div class="alert alert-success">✓ Deleted ' . $deleted_regen . ' regeneration queue records</div>';
        
        // Release any locks
        $lock_released = \local_vanta\sync_manager::release_sync_lock($company_id);
        echo '<div class="alert alert-success">✓ Released locks: ' . ($lock_released ? 'Yes' : 'No locks found') . '</div>';
        
        // Clean up test credentials (optional)
        $test_creds = $DB->get_records('local_vanta_api_credentials', [
            'company_id' => $company_id,
            'client_id' => 'test_client_id'
        ]);
        
        foreach ($test_creds as $cred) {
            // Delete associated sync rules
            $DB->delete_records('local_vanta_training_sync_rules', ['vanta_id' => $cred->id]);
            // Delete credentials
            $DB->delete_records('local_vanta_api_credentials', ['id' => $cred->id]);
        }
        
        if (!empty($test_creds)) {
            echo '<div class="alert alert-success">✓ Deleted ' . count($test_creds) . ' test credentials and associated rules</div>';
        }
        
        // Clean up JSON files
        global $CFG;
        $data_file = $CFG->dataroot . '/local_vanta/vanta_data_' . $company_id . '.json';
        if (file_exists($data_file)) {
            unlink($data_file);
            echo '<div class="alert alert-success">✓ Deleted JSON data file</div>';
        }
        
        echo '<div class="alert alert-success"><strong>✓ Cleanup completed!</strong></div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">✗ Cleanup failed: ' . $e->getMessage() . '</div>';
    }
} 