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
 * Scheduled task for Vanta sync operations
 *
 * @package    local_vanta
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vanta\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');

/**
 * Scheduled task for processing Vanta sync queue and data regeneration
 */
class sync_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sync_task', 'local_vanta');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        
        mtrace('Starting Vanta sync task...');
        
        try {
            // Process queued completions
            $this->process_queue();
            
            // Check for companies that need data regeneration
            $this->check_data_regeneration();
            
            // Clean up old queue records
            $this->cleanup_old_records();
            
            mtrace('Vanta sync task completed successfully.');
            
        } catch (\Exception $e) {
            mtrace('Error in Vanta sync task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process queued completions
     */
    private function process_queue() {
        global $DB;
        
        mtrace('Processing Vanta sync queue...');
        
        // Get all companies with pending queue items
        $sql = "SELECT DISTINCT company_id 
                FROM {local_vanta_sync_queue} 
                WHERE status = 'pending'";
        
        $companies = $DB->get_fieldset_sql($sql);
        
        if (empty($companies)) {
            mtrace('No pending queue items found.');
            return;
        }
        
        $total_processed = 0;
        $total_successful = 0;
        $total_failed = 0;
        
        foreach ($companies as $company_id) {
            mtrace("Processing queue for company ID: $company_id");
            
            // Check if sync is locked for this company
            if (\local_vanta\sync_manager::is_sync_locked($company_id)) {
                mtrace("Sync is locked for company $company_id, skipping queue processing.");
                continue;
            }
            
            // Process queue for this company
            $results = \local_vanta\sync_manager::process_queue($company_id, 100);
            
            $total_processed += $results['processed'];
            $total_successful += $results['successful'];
            $total_failed += $results['failed'];
            
            mtrace("Company $company_id: Processed {$results['processed']}, " .
                   "Successful {$results['successful']}, Failed {$results['failed']}");
            
            if (!empty($results['errors'])) {
                foreach ($results['errors'] as $error) {
                    mtrace("Error: $error");
                }
            }
        }
        
        mtrace("Queue processing complete. Total: Processed $total_processed, " .
               "Successful $total_successful, Failed $total_failed");
    }

    /**
     * Check for companies that need data regeneration
     */
    private function check_data_regeneration() {
        global $DB;
        
        mtrace('Checking for data regeneration needs...');
        
        // Get companies that have a regeneration flag set
        $companies_to_regenerate = $DB->get_records(
            'local_vanta_regeneration_queue',
            ['status' => 'pending'],
            'queued_at ASC'
        );
        
        if (empty($companies_to_regenerate)) {
            mtrace('No data regeneration needed.');
            return;
        }
        
        foreach ($companies_to_regenerate as $regen_record) {
            $company_id = $regen_record->company_id;
            
            mtrace("Starting data regeneration for company ID: $company_id");
            
            try {
                // Mark as processing
                $regen_record->status = 'processing';
                $regen_record->started_at = time();
                $DB->update_record('local_vanta_regeneration_queue', $regen_record);
                
                // Regenerate data
                $success = \local_vanta\sync_manager::regenerate_completion_data($company_id);
                
                if ($success) {
                    // Mark as completed and remove from queue
                    $DB->delete_records('local_vanta_regeneration_queue', ['id' => $regen_record->id]);
                    mtrace("Data regeneration completed successfully for company $company_id");
                    
                    // Trigger API sync with new data
                    $this->sync_regenerated_data($company_id);
                    
                } else {
                    // Mark as failed
                    $regen_record->status = 'failed';
                    $regen_record->error_message = 'Data regeneration failed';
                    $regen_record->completed_at = time();
                    $DB->update_record('local_vanta_regeneration_queue', $regen_record);
                    mtrace("Data regeneration failed for company $company_id");
                }
                
            } catch (\Exception $e) {
                // Mark as failed with error
                $regen_record->status = 'failed';
                $regen_record->error_message = $e->getMessage();
                $regen_record->completed_at = time();
                $DB->update_record('local_vanta_regeneration_queue', $regen_record);
                mtrace("Data regeneration failed for company $company_id: " . $e->getMessage());
            }
        }
    }

    /**
     * Sync regenerated data to Vanta API
     *
     * @param int $company_id Company ID
     */
    private function sync_regenerated_data($company_id) {
        mtrace("Syncing regenerated data to Vanta for company $company_id");
        
        try {
            // Load the regenerated data
            $data = \local_vanta\sync_manager::load_completion_data($company_id);
            
            if (!$data || empty($data['data'])) {
                mtrace("No data found to sync for company $company_id");
                return;
            }
            
            // The regenerated data is already in Vanta API format, send it directly with company_id
            $success = \vanta_helper::send_training_completion($data['data'], null, $company_id);
            
            if ($success) {
                mtrace("Successfully synced " . count($data['data']) . " completions to Vanta for company $company_id");
            } else {
                mtrace("Failed to sync completions to Vanta for company $company_id");
            }
            
        } catch (\Exception $e) {
            mtrace("Error syncing regenerated data for company $company_id: " . $e->getMessage());
        }
    }

    /**
     * Clean up old queue records
     */
    private function cleanup_old_records() {
        global $DB;
        
        mtrace('Cleaning up old queue records...');
        
        try {
            // Clean up completed/failed queue records older than 30 days
            $cleaned_queue = \local_vanta\sync_manager::cleanup_queue(30);
            mtrace("Cleaned up $cleaned_queue old queue records.");
            
            // Clean up old regeneration queue records
            $cutoff_time = time() - (30 * 86400); // 30 days
            $cleaned_regen = $DB->delete_records_select(
                'local_vanta_regeneration_queue',
                'status IN (?, ?) AND completed_at < ?',
                ['completed', 'failed', $cutoff_time]
            );
            mtrace("Cleaned up $cleaned_regen old regeneration queue records.");
            
        } catch (\Exception $e) {
            mtrace("Error during cleanup: " . $e->getMessage());
        }
    }
} 