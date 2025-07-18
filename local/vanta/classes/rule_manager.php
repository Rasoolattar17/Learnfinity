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
 * Rule manager for handling training sync rule changes
 *
 * @package    local_vanta
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vanta;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');

/**
 * Manages training sync rule changes and triggers data regeneration
 */
class rule_manager {

    /**
     * Update training sync rule and trigger data regeneration if needed
     *
     * @param int $rule_id Rule ID
     * @param array $rule_data New rule data
     * @param int $company_id Company ID
     * @return bool True on success, false on failure
     */
    public static function update_rule($rule_id, $rule_data, $company_id) {
        global $DB, $USER;
        
        try {
            // Get current rule for comparison
            $current_rule = $DB->get_record('local_vanta_training_sync_rules', ['id' => $rule_id]);
            
            if (!$current_rule) {
                return false;
            }
            
            // Check if rule changes affect data generation
            $needs_regeneration = self::rule_affects_data($current_rule, $rule_data);
            
            // Update the rule
            $update_record = new \stdClass();
            $update_record->id = $rule_id;
            $update_record->frameworks = $rule_data['frameworks'] ?? $current_rule->frameworks;
            $update_record->courses = $rule_data['courses'] ?? $current_rule->courses;
            $update_record->completion_mode = $rule_data['completion_mode'] ?? $current_rule->completion_mode;
            $update_record->resource_id = $rule_data['resource_id'] ?? $current_rule->resource_id;
            $update_record->modified_by = $USER->id ?? 0;
            $update_record->modified_on = time();
            
            $success = $DB->update_record('local_vanta_training_sync_rules', $update_record);
            
            if ($success && $needs_regeneration) {
                // Queue data regeneration
                self::queue_data_regeneration($company_id, 'rule_change', $USER->id ?? 0);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create new training sync rule and trigger initial data generation
     *
     * @param array $rule_data Rule data
     * @param int $company_id Company ID
     * @return int|false Rule ID on success, false on failure
     */
    public static function create_rule($rule_data, $company_id) {
        global $DB, $USER;
        
        try {
            // Create the rule
            $record = new \stdClass();
            $record->vanta_id = $rule_data['vanta_id'];
            $record->frameworks = $rule_data['frameworks'] ?? '';
            $record->courses = $rule_data['courses'] ?? '';
            $record->completion_mode = $rule_data['completion_mode'] ?? 'any';
            $record->resource_id = $rule_data['resource_id'] ?? '';
            $record->created_by = $USER->id ?? 0;
            $record->created_on = time();
            $record->modified_by = $USER->id ?? 0;
            $record->modified_on = time();
            
            $rule_id = $DB->insert_record('local_vanta_training_sync_rules', $record);
            
            if ($rule_id) {
                // Queue initial data generation
                self::queue_data_regeneration($company_id, 'rule_creation', $USER->id ?? 0);
            }
            
            return $rule_id;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete training sync rule and clean up associated data
     *
     * @param int $rule_id Rule ID
     * @param int $company_id Company ID
     * @return bool True on success, false on failure
     */
    public static function delete_rule($rule_id, $company_id) {
        global $DB;
        
        try {
            // Delete the rule
            $success = $DB->delete_records('local_vanta_training_sync_rules', ['id' => $rule_id]);
            
            if ($success) {
                // Clean up associated data files
                sync_manager::store_completion_data($company_id, []);
                
                // Queue regeneration to clear Vanta data
                self::queue_data_regeneration($company_id, 'rule_deletion', $USER->id ?? 0);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if rule changes affect data generation
     *
     * @param \stdClass $current_rule Current rule
     * @param array $new_rule_data New rule data
     * @return bool True if regeneration needed, false otherwise
     */
    private static function rule_affects_data($current_rule, $new_rule_data) {
        // Check if courses changed
        $current_courses = $current_rule->courses ?? '';
        $new_courses = $new_rule_data['courses'] ?? $current_courses;
        
        if ($current_courses !== $new_courses) {
            return true;
        }
        
        // Check if completion mode changed
        $current_mode = $current_rule->completion_mode ?? 'any';
        $new_mode = $new_rule_data['completion_mode'] ?? $current_mode;
        
        if ($current_mode !== $new_mode) {
            return true;
        }
        
        // Frameworks don't affect data generation in current implementation
        // but could be added here if needed
        
        return false;
    }

    /**
     * Queue data regeneration for a company
     *
     * @param int $company_id Company ID
     * @param string $reason Reason for regeneration
     * @param int $triggered_by User ID who triggered the regeneration
     * @return bool True on success, false on failure
     */
    public static function queue_data_regeneration($company_id, $reason, $triggered_by = 0) {
        global $DB;
        
        try {
            // Check if already queued
            $existing = $DB->get_record('local_vanta_regeneration_queue', [
                'company_id' => $company_id,
                'status' => 'pending'
            ]);
            
            if ($existing) {
                // Update existing record
                $existing->reason = $reason;
                $existing->queued_at = time();
                $existing->triggered_by = $triggered_by;
                return $DB->update_record('local_vanta_regeneration_queue', $existing);
            }
            
            // Create new regeneration queue record
            $record = new \stdClass();
            $record->company_id = $company_id;
            $record->reason = $reason;
            $record->status = 'pending';
            $record->queued_at = time();
            $record->triggered_by = $triggered_by;
            
            return $DB->insert_record('local_vanta_regeneration_queue', $record) !== false;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get regeneration queue status for a company
     *
     * @param int $company_id Company ID
     * @return array|false Queue status or false if not found
     */
    public static function get_regeneration_status($company_id) {
        global $DB;
        
        $record = $DB->get_record('local_vanta_regeneration_queue', [
            'company_id' => $company_id
        ], '*', IGNORE_MULTIPLE);
        
        if (!$record) {
            return false;
        }
        
        return [
            'status' => $record->status,
            'queued_at' => $record->queued_at,
            'started_at' => $record->started_at,
            'completed_at' => $record->completed_at,
            'reason' => $record->reason,
            'error_message' => $record->error_message
        ];
    }

    /**
     * Manually trigger data regeneration for a company
     *
     * @param int $company_id Company ID
     * @param bool $force_immediate Whether to force immediate regeneration
     * @return bool True on success, false on failure
     */
    public static function trigger_regeneration($company_id, $force_immediate = false) {
        global $USER;
        
        if ($force_immediate) {
            // Attempt immediate regeneration
            return sync_manager::regenerate_completion_data($company_id);
        } else {
            // Queue for background processing
            return self::queue_data_regeneration($company_id, 'manual_trigger', $USER->id ?? 0);
        }
    }

    /**
     * Get rule change history for a company
     *
     * @param int $company_id Company ID
     * @param int $limit Number of records to return
     * @return array Rule change history
     */
    public static function get_rule_history($company_id, $limit = 50) {
        global $DB;
        
        // Get Vanta ID for company
        $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
        
        if (!$vanta_id) {
            return [];
        }
        
        // Get rule changes from regeneration queue
        $sql = "SELECT rq.*, u.firstname, u.lastname, u.email
                FROM {local_vanta_regeneration_queue} rq
                LEFT JOIN {user} u ON u.id = rq.triggered_by
                WHERE rq.company_id = ?
                ORDER BY rq.queued_at DESC";
        
        return $DB->get_records_sql($sql, [$company_id], 0, $limit);
    }
} 