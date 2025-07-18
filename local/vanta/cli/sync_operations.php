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
 * CLI script for Vanta sync operations
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
    'operation' => '',
    'company-id' => 0,
    'force' => false,
    'limit' => 100,
    'status' => false,
    'cleanup' => false,
    'days' => 30
], [
    'h' => 'help',
    'o' => 'operation',
    'c' => 'company-id',
    'f' => 'force',
    'l' => 'limit',
    's' => 'status',
    'd' => 'days'
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
Vanta Sync Operations CLI Script

Options:
-h, --help              Print out this help
-o, --operation         Operation to perform:
                        - process-queue: Process pending queue items
                        - regenerate: Regenerate completion data
                        - status: Show sync status
                        - cleanup: Clean up old records
                        - lock-status: Show lock status
                        - release-lock: Release sync lock
-c, --company-id        Company ID (0 for all companies)
-f, --force             Force immediate operation (bypass queue)
-l, --limit             Limit number of items to process (default: 100)
-s, --status            Show detailed status information
-d, --days              Number of days for cleanup operations (default: 30)

Examples:
php sync_operations.php --operation=process-queue --company-id=1
php sync_operations.php --operation=regenerate --company-id=1 --force
php sync_operations.php --operation=status --company-id=1
php sync_operations.php --operation=cleanup --days=30
php sync_operations.php --operation=lock-status --company-id=1
php sync_operations.php --operation=release-lock --company-id=1

";

    echo $help;
    exit(0);
}

if (empty($options['operation'])) {
    cli_error('Operation is required. Use --help for more information.');
}

$operation = $options['operation'];
$company_id = (int)$options['company-id'];
$force = $options['force'];
$limit = (int)$options['limit'];
$show_status = $options['status'];
$days = (int)$options['days'];

// Validate company ID if specified
if ($company_id > 0) {
    $company_exists = $DB->record_exists('local_vanta_api_credentials', ['company_id' => $company_id]);
    if (!$company_exists) {
        cli_error("Company ID $company_id not found in Vanta credentials.");
    }
}

switch ($operation) {
    case 'process-queue':
        process_queue($company_id, $limit, $show_status);
        break;
        
    case 'regenerate':
        regenerate_data($company_id, $force, $show_status);
        break;
        
    case 'status':
        show_status($company_id);
        break;
        
    case 'cleanup':
        cleanup_records($days, $show_status);
        break;
        
    case 'lock-status':
        show_lock_status($company_id);
        break;
        
    case 'release-lock':
        release_lock($company_id);
        break;
        
    default:
        cli_error("Unknown operation: $operation. Use --help for available operations.");
}

/**
 * Process queue items
 */
function process_queue($company_id, $limit, $show_status) {
    global $DB;
    
    cli_heading('Processing Vanta Sync Queue');
    
    if ($company_id > 0) {
        cli_writeln("Processing queue for company ID: $company_id");
        $results = \local_vanta\sync_manager::process_queue($company_id, $limit);
        display_queue_results($results, $company_id);
    } else {
        cli_writeln("Processing queue for all companies");
        
        // Get all companies with pending items
        $sql = "SELECT DISTINCT company_id FROM {local_vanta_sync_queue} WHERE status = 'pending'";
        $companies = $DB->get_fieldset_sql($sql);
        
        if (empty($companies)) {
            cli_writeln("No pending queue items found.");
            return;
        }
        
        $total_results = ['processed' => 0, 'successful' => 0, 'failed' => 0, 'errors' => []];
        
        foreach ($companies as $cid) {
            cli_writeln("Processing company ID: $cid");
            $results = \local_vanta\sync_manager::process_queue($cid, $limit);
            
            $total_results['processed'] += $results['processed'];
            $total_results['successful'] += $results['successful'];
            $total_results['failed'] += $results['failed'];
            $total_results['errors'] = array_merge($total_results['errors'], $results['errors']);
            
            display_queue_results($results, $cid);
        }
        
        cli_writeln("\nTotal Results:");
        display_queue_results($total_results, 'all');
    }
    
    if ($show_status) {
        show_queue_stats($company_id);
    }
}

/**
 * Regenerate completion data
 */
function regenerate_data($company_id, $force, $show_status) {
    global $DB;
    
    cli_heading('Regenerating Vanta Completion Data');
    
    if ($company_id > 0) {
        cli_writeln("Regenerating data for company ID: $company_id");
        
        if ($force) {
            cli_writeln("Force mode: Attempting immediate regeneration");
            $success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
            
            if ($success) {
                cli_writeln("✓ Data regeneration completed successfully");
            } else {
                cli_error("✗ Data regeneration failed");
            }
        } else {
            cli_writeln("Queue mode: Adding to regeneration queue");
            $success = \local_vanta\rule_manager::queue_data_regeneration($company_id, 'cli_trigger', 0);
            
            if ($success) {
                cli_writeln("✓ Data regeneration queued successfully");
            } else {
                cli_error("✗ Failed to queue data regeneration");
            }
        }
    } else {
        // Get all companies
        $companies = $DB->get_fieldset('local_vanta_api_credentials', 'company_id', []);
        
        if (empty($companies)) {
            cli_writeln("No companies found with Vanta credentials.");
            return;
        }
        
        foreach ($companies as $cid) {
            cli_writeln("Regenerating data for company ID: $cid");
            
            if ($force) {
                $success = \local_vanta\sync_manager::regenerate_completion_data($cid);
            } else {
                $success = \local_vanta\rule_manager::queue_data_regeneration($cid, 'cli_trigger', 0);
            }
            
            if ($success) {
                cli_writeln("✓ Company $cid: Success");
            } else {
                cli_writeln("✗ Company $cid: Failed");
            }
        }
    }
    
    if ($show_status) {
        show_regeneration_status($company_id);
    }
}

/**
 * Show sync status
 */
function show_status($company_id) {
    cli_heading('Vanta Sync Status');
    
    if ($company_id > 0) {
        show_company_status($company_id);
    } else {
        show_all_companies_status();
    }
}

/**
 * Clean up old records
 */
function cleanup_records($days, $show_status) {
    cli_heading('Cleaning Up Old Records');
    
    cli_writeln("Cleaning up records older than $days days");
    
    // Clean up queue records
    $cleaned_queue = \local_vanta\sync_manager::cleanup_queue($days);
    cli_writeln("✓ Cleaned up $cleaned_queue old queue records");
    
    // Clean up regeneration queue records
    global $DB;
    $cutoff_time = time() - ($days * 86400);
    $cleaned_regen = $DB->delete_records_select(
        'local_vanta_regeneration_queue',
        'status IN (?, ?) AND completed_at < ?',
        ['completed', 'failed', $cutoff_time]
    );
    cli_writeln("✓ Cleaned up $cleaned_regen old regeneration queue records");
    
    if ($show_status) {
        show_queue_stats(0);
    }
}

/**
 * Show lock status
 */
function show_lock_status($company_id) {
    global $DB;
    
    cli_heading('Sync Lock Status');
    
    if ($company_id > 0) {
        $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
        cli_writeln("Company ID $company_id: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
        
        if ($locked) {
            // Try to get lock details
            global $CFG;
            $lock_file = $CFG->dataroot . '/local_vanta/vanta_sync_lock_' . $company_id . '.lock';
            if (file_exists($lock_file)) {
                $lock_data = json_decode(file_get_contents($lock_file), true);
                if ($lock_data) {
                    cli_writeln("Lock details:");
                    cli_writeln("  Operation: " . ($lock_data['operation'] ?? 'unknown'));
                    cli_writeln("  User ID: " . ($lock_data['user_id'] ?? 'unknown'));
                    cli_writeln("  Timestamp: " . date('Y-m-d H:i:s', $lock_data['timestamp'] ?? 0));
                    cli_writeln("  PID: " . ($lock_data['pid'] ?? 'unknown'));
                }
            }
        }
    } else {
        $companies = $DB->get_fieldset('local_vanta_api_credentials', 'company_id', []);
        
        foreach ($companies as $cid) {
            $locked = \local_vanta\sync_manager::is_sync_locked($cid);
            cli_writeln("Company ID $cid: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
        }
    }
}

/**
 * Release sync lock
 */
function release_lock($company_id) {
    cli_heading('Releasing Sync Lock');
    
    if ($company_id <= 0) {
        cli_error("Company ID is required for lock release operation.");
    }
    
    $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
    
    if (!$locked) {
        cli_writeln("Company ID $company_id is not locked.");
        return;
    }
    
    $success = \local_vanta\sync_manager::release_sync_lock($company_id);
    
    if ($success) {
        cli_writeln("✓ Lock released successfully for company ID $company_id");
    } else {
        cli_error("✗ Failed to release lock for company ID $company_id");
    }
}

/**
 * Display queue processing results
 */
function display_queue_results($results, $company_id) {
    cli_writeln("Results for company $company_id:");
    cli_writeln("  Processed: {$results['processed']}");
    cli_writeln("  Successful: {$results['successful']}");
    cli_writeln("  Failed: {$results['failed']}");
    
    if (!empty($results['errors'])) {
        cli_writeln("  Errors:");
        foreach ($results['errors'] as $error) {
            cli_writeln("    - $error");
        }
    }
}

/**
 * Show queue statistics
 */
function show_queue_stats($company_id) {
    cli_writeln("\nQueue Statistics:");
    
    if ($company_id > 0) {
        $stats = \local_vanta\sync_manager::get_queue_stats($company_id);
        cli_writeln("Company ID $company_id:");
        cli_writeln("  Pending: {$stats['pending']}");
        cli_writeln("  Completed: {$stats['completed']}");
        cli_writeln("  Failed: {$stats['failed']}");
    } else {
        $stats = \local_vanta\sync_manager::get_queue_stats();
        cli_writeln("All companies:");
        cli_writeln("  Pending: {$stats['pending']}");
        cli_writeln("  Completed: {$stats['completed']}");
        cli_writeln("  Failed: {$stats['failed']}");
    }
}

/**
 * Show company status
 */
function show_company_status($company_id) {
    global $DB;
    
    cli_writeln("Company ID: $company_id");
    
    // Lock status
    $locked = \local_vanta\sync_manager::is_sync_locked($company_id);
    cli_writeln("Lock Status: " . ($locked ? 'LOCKED' : 'UNLOCKED'));
    
    // Queue stats
    $queue_stats = \local_vanta\sync_manager::get_queue_stats($company_id);
    cli_writeln("Queue Stats:");
    cli_writeln("  Pending: {$queue_stats['pending']}");
    cli_writeln("  Completed: {$queue_stats['completed']}");
    cli_writeln("  Failed: {$queue_stats['failed']}");
    
    // Regeneration status
    $regen_status = \local_vanta\rule_manager::get_regeneration_status($company_id);
    if ($regen_status) {
        cli_writeln("Regeneration Status: {$regen_status['status']}");
        if ($regen_status['queued_at']) {
            cli_writeln("  Queued: " . date('Y-m-d H:i:s', $regen_status['queued_at']));
        }
        if ($regen_status['started_at']) {
            cli_writeln("  Started: " . date('Y-m-d H:i:s', $regen_status['started_at']));
        }
        if ($regen_status['completed_at']) {
            cli_writeln("  Completed: " . date('Y-m-d H:i:s', $regen_status['completed_at']));
        }
    } else {
        cli_writeln("Regeneration Status: None");
    }
    
    // Data file status
    $data = \local_vanta\sync_manager::load_completion_data($company_id);
    if ($data) {
        cli_writeln("Data File:");
        cli_writeln("  Generated: " . date('Y-m-d H:i:s', $data['generated_at']));
        cli_writeln("  Records: {$data['completion_count']}");
    } else {
        cli_writeln("Data File: Not found");
    }
}

/**
 * Show all companies status
 */
function show_all_companies_status() {
    global $DB;
    
    $companies = $DB->get_fieldset('local_vanta_api_credentials', 'company_id', []);
    
    if (empty($companies)) {
        cli_writeln("No companies found with Vanta credentials.");
        return;
    }
    
    foreach ($companies as $company_id) {
        cli_writeln(str_repeat('-', 50));
        show_company_status($company_id);
    }
}

/**
 * Show regeneration status
 */
function show_regeneration_status($company_id) {
    cli_writeln("\nRegeneration Status:");
    
    if ($company_id > 0) {
        $status = \local_vanta\rule_manager::get_regeneration_status($company_id);
        if ($status) {
            cli_writeln("Company ID $company_id: {$status['status']}");
            if ($status['error_message']) {
                cli_writeln("  Error: {$status['error_message']}");
            }
        } else {
            cli_writeln("Company ID $company_id: No regeneration in progress");
        }
    } else {
        global $DB;
        $companies = $DB->get_fieldset('local_vanta_api_credentials', 'company_id', []);
        
        foreach ($companies as $cid) {
            $status = \local_vanta\rule_manager::get_regeneration_status($cid);
            if ($status) {
                cli_writeln("Company ID $cid: {$status['status']}");
            } else {
                cli_writeln("Company ID $cid: No regeneration in progress");
            }
        }
    }
} 