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
 * CLI test script for Vanta integration system
 *
 * @package    local_vanta
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');
require_once($CFG->dirroot . '/local/vanta/classes/rule_manager.php');

// Get CLI options.
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'test' => '',
    'company-id' => 1,
    'all' => false
], [
    'h' => 'help',
    't' => 'test',
    'c' => 'company-id',
    'a' => 'all'
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
Vanta Integration System Testing CLI Script

Options:
-h, --help              Print out this help
-t, --test              Specific test to run:
                        - database: Test database schema
                        - classes: Test class loading
                        - lock: Test lock mechanism
                        - queue: Test queue system
                        - json: Test JSON generation
                        - integration: Test full integration
                        - cleanup: Clean up test data
-c, --company-id        Company ID for testing (default: 1)
-a, --all               Run all tests

Examples:
php test_system.php --test=database
php test_system.php --test=lock --company-id=1
php test_system.php --all --company-id=1

";

    echo $help;
    exit(0);
}

$test = $options['test'];
$company_id = (int)$options['company-id'];
$run_all = $options['all'];

cli_heading('Vanta Integration System Testing');

if ($run_all) {
    cli_writeln("Running all tests for company ID: $company_id");
    cli_writeln(str_repeat('=', 60));
    
    $tests = ['database', 'classes', 'lock', 'queue', 'json', 'integration'];
    
    foreach ($tests as $test_name) {
        cli_writeln("");
        cli_heading("Test: " . ucfirst($test_name));
        run_test($test_name, $company_id);
        cli_writeln(str_repeat('-', 40));
    }
    
    cli_writeln("");
    cli_writeln("All tests completed!");
    
} else if (!empty($test)) {
    cli_writeln("Running test: $test for company ID: $company_id");
    cli_writeln(str_repeat('=', 60));
    run_test($test, $company_id);
    
} else {
    cli_error('Please specify a test with --test or use --all to run all tests. Use --help for more information.');
}

/**
 * Run a specific test
 */
function run_test($test, $company_id) {
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
            
        default:
            cli_error("Unknown test: $test");
    }
}

/**
 * Test database schema
 */
function test_database_schema() {
    global $DB;
    
    cli_writeln('Testing Database Schema...');
    
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
            cli_writeln("✓ Table '$table' exists");
            
            // Check table structure
            $columns = $DB->get_columns($table);
            cli_writeln("  Columns: " . count($columns));
            
        } else {
            cli_writeln("✗ Table '$table' missing");
            $all_exist = false;
        }
    }
    
    if ($all_exist) {
        cli_writeln("✓ All database tables exist and are properly structured!");
    } else {
        cli_writeln("✗ Some database tables are missing. Run upgrade.php");
    }
}

/**
 * Test class loading
 */
function test_class_loading() {
    cli_writeln('Testing Class Loading...');
    
    $classes = [
        'local_vanta\sync_manager',
        'local_vanta\rule_manager',
        'local_vanta\task\sync_task'
    ];
    
    $all_loaded = true;
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            cli_writeln("✓ Class '$class' loaded successfully");
            
            // Test basic methods
            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            cli_writeln("  Public methods: " . count($methods));
            
        } else {
            cli_writeln("✗ Class '$class' not found");
            $all_loaded = false;
        }
    }
    
    if ($all_loaded) {
        cli_writeln("✓ All classes loaded successfully!");
    }
}

/**
 * Test lock mechanism
 */
function test_lock_mechanism($company_id) {
    cli_writeln("Testing Lock Mechanism for Company $company_id...");
    
    try {
        // Test 1: Check initial lock status
        $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
        cli_writeln("Initial lock status: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
        
        // Test 2: Acquire lock
        $acquired = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'test_operation');
        if ($acquired) {
            cli_writeln("✓ Lock acquired successfully");
            
            // Test 3: Check lock status
            $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
            cli_writeln("Lock status after acquire: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
            
            // Test 4: Try to acquire again (should fail)
            $acquired_again = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'test_operation_2');
            if (!$acquired_again) {
                cli_writeln("✓ Second lock acquisition correctly failed");
            } else {
                cli_writeln("⚠ Second lock acquisition should have failed");
            }
            
            // Test 5: Release lock
            $released = \local_vanta\sync_manager::release_sync_lock($company_id);
            if ($released) {
                cli_writeln("✓ Lock released successfully");
                
                // Test 6: Check final status
                $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
                cli_writeln("Final lock status: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
            } else {
                cli_writeln("✗ Failed to release lock");
            }
            
        } else {
            cli_writeln("✗ Failed to acquire lock");
        }
        
        cli_writeln("✓ Lock mechanism test completed!");
        
    } catch (Exception $e) {
        cli_writeln("✗ Lock test failed: " . $e->getMessage());
    }
}

/**
 * Test queue system
 */
function test_queue_system($company_id) {
    global $DB;
    
    cli_writeln("Testing Queue System for Company $company_id...");
    
    try {
        // Test 1: Check initial queue stats
        $initial_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        cli_writeln("Initial queue stats: Pending={$initial_stats['pending']}, " .
                   "Completed={$initial_stats['completed']}, Failed={$initial_stats['failed']}");
        
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
                cli_writeln("✓ Queued test item: User {$item['userid']}, Course {$item['courseid']}");
            } else {
                cli_writeln("✗ Failed to queue test item");
            }
        }
        
        // Test 3: Check queue stats after adding
        $after_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        cli_writeln("Queue stats after adding: Pending={$after_stats['pending']}, " .
                   "Completed={$after_stats['completed']}, Failed={$after_stats['failed']}");
        
        // Test 4: Process queue (this will fail because test users/courses don't exist, but that's expected)
        cli_writeln("Processing queue (expected to fail for test data)...");
        $results = \local_vanta\sync_manager::process_queue($company_id, 10);
        cli_writeln("Process results: Processed={$results['processed']}, " .
                   "Successful={$results['successful']}, Failed={$results['failed']}");
        
        // Test 5: Final queue stats
        $final_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        cli_writeln("Final queue stats: Pending={$final_stats['pending']}, " .
                   "Completed={$final_stats['completed']}, Failed={$final_stats['failed']}");
        
        cli_writeln("✓ Queue system test completed!");
        
    } catch (Exception $e) {
        cli_writeln("✗ Queue test failed: " . $e->getMessage());
    }
}

/**
 * Test JSON generation
 */
function test_json_generation($company_id) {
    global $DB;
    
    cli_writeln("Testing JSON Generation for Company $company_id...");
    
    try {
        // Test 1: Check if company has Vanta credentials
        $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
        if (!$vanta_id) {
            cli_writeln("⚠ No Vanta credentials found for company $company_id. Creating test credentials...");
            
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
            cli_writeln("✓ Test credentials created with ID: $vanta_id");
        } else {
            cli_writeln("✓ Vanta credentials found with ID: $vanta_id");
        }
        
        // Test 2: Check if sync rule exists
        $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]);
        if (!$sync_rule) {
            cli_writeln("⚠ No sync rule found. Creating test sync rule...");
            
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
            cli_writeln("✓ Test sync rule created with ID: $rule_id");
            
            $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['id' => $rule_id]);
        } else {
            cli_writeln("✓ Sync rule found with ID: {$sync_rule->id}");
        }
        
        // Test 3: Generate completion data
        cli_writeln("Attempting to regenerate completion data...");
        $success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
        
        if ($success) {
            cli_writeln("✓ Completion data generated successfully");
            
            // Test 4: Load and display data
            $data = \local_vanta\sync_manager::load_completion_data($company_id);
            if ($data) {
                cli_writeln("✓ JSON data loaded successfully");
                cli_writeln("Generated At: " . date('Y-m-d H:i:s', $data['generated_at']));
                cli_writeln("Record Count: {$data['completion_count']}");
                
                if (!empty($data['data'])) {
                    cli_writeln("Sample Data (first record):");
                    cli_writeln(json_encode($data['data'][0], JSON_PRETTY_PRINT));
                } else {
                    cli_writeln("No completion data found (this is normal if no users have completed the test courses)");
                }
            } else {
                cli_writeln("⚠ No JSON data file found");
            }
        } else {
            cli_writeln("✗ Failed to generate completion data");
        }
        
        cli_writeln("✓ JSON generation test completed!");
        
    } catch (Exception $e) {
        cli_writeln("✗ JSON generation test failed: " . $e->getMessage());
    }
}

/**
 * Test full integration
 */
function test_full_integration($company_id) {
    cli_writeln("Testing Full Integration for Company $company_id...");
    
    try {
        // Test 1: Lock acquisition
        cli_writeln("Step 1: Testing lock acquisition...");
        $locked = \local_vanta\sync_manager::acquire_sync_lock($company_id, 'integration_test');
        if ($locked) {
            cli_writeln("✓ Lock acquired");
        } else {
            cli_writeln("⚠ Lock already exists or failed to acquire");
        }
        
        // Test 2: Queue some test completions
        cli_writeln("Step 2: Testing queue during lock...");
        $queued = \local_vanta\sync_manager::queue_completion(99, 99, $company_id, 'integration_test');
        if ($queued) {
            cli_writeln("✓ Completion queued during lock");
        } else {
            cli_writeln("✗ Failed to queue completion");
        }
        
        // Test 3: Release lock
        cli_writeln("Step 3: Releasing lock...");
        $released = \local_vanta\sync_manager::release_sync_lock($company_id);
        if ($released) {
            cli_writeln("✓ Lock released");
        } else {
            cli_writeln("✗ Failed to release lock");
        }
        
        // Test 4: Process queue
        cli_writeln("Step 4: Processing queue...");
        $results = \local_vanta\sync_manager::process_queue($company_id, 5);
        cli_writeln("Queue processing results: " . json_encode($results));
        
        // Test 5: Data regeneration
        cli_writeln("Step 5: Testing data regeneration...");
        $regen_success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
        if ($regen_success) {
            cli_writeln("✓ Data regeneration successful");
        } else {
            cli_writeln("⚠ Data regeneration failed (may be due to missing test data)");
        }
        
        cli_writeln("✓ Full integration test completed!");
        
    } catch (Exception $e) {
        cli_writeln("✗ Integration test failed: " . $e->getMessage());
        
        // Ensure lock is released
        \local_vanta\sync_manager::release_sync_lock($company_id);
    }
}

/**
 * Clean up test data
 */
function test_cleanup($company_id) {
    global $DB;
    
    cli_writeln("Cleaning Up Test Data for Company $company_id...");
    
    try {
        // Clean up queue
        $deleted_queue = $DB->delete_records('local_vanta_sync_queue', ['company_id' => $company_id]);
        cli_writeln("✓ Deleted $deleted_queue queue records");
        
        // Clean up regeneration queue
        $deleted_regen = $DB->delete_records('local_vanta_regeneration_queue', ['company_id' => $company_id]);
        cli_writeln("✓ Deleted $deleted_regen regeneration queue records");
        
        // Release any locks
        $lock_released = \local_vanta\sync_manager::release_sync_lock($company_id);
        cli_writeln("✓ Released locks: " . ($lock_released ? 'Yes' : 'No locks found'));
        
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
            cli_writeln("✓ Deleted " . count($test_creds) . " test credentials and associated rules");
        }
        
        // Clean up JSON files
        global $CFG;
        $data_file = $CFG->dataroot . '/local_vanta/vanta_data_' . $company_id . '.json';
        if (file_exists($data_file)) {
            unlink($data_file);
            cli_writeln("✓ Deleted JSON data file");
        }
        
        cli_writeln("✓ Cleanup completed!");
        
    } catch (Exception $e) {
        cli_writeln("✗ Cleanup failed: " . $e->getMessage());
    }
} 