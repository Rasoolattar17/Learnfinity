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
 * Vanta sync manager for handling data synchronization with locking and queuing
 *
 * @package    local_vanta
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vanta;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/vanta/lib.php');

/**
 * Manages Vanta synchronization with locking, queuing, and JSON file handling
 */
class sync_manager {

    /** @var string Lock file prefix */
    const LOCK_PREFIX = 'vanta_sync_lock_';
    
    /** @var string JSON data file prefix */
    const DATA_FILE_PREFIX = 'vanta_data_';
    
    /** @var string Queue table name */
    const QUEUE_TABLE = 'local_vanta_sync_queue';
    
    /** @var int Lock timeout in seconds */
    const LOCK_TIMEOUT = 3600; // 1 hour
    
    /** @var int Batch size for processing large datasets */
    const BATCH_SIZE = 1000;

    /** @var int Memory limit check interval */
    const MEMORY_LIMIT_CHECK = 100; // Check memory every 100 records
    
    /** @var float Memory usage threshold */
    const MAX_MEMORY_USAGE = 0.8; // 80% of memory limit
    
    /** @var int JSON chunk size */
    const JSON_CHUNK_SIZE = 500; // Records per JSON chunk

    /**
     * Check if sync is currently locked for a company
     *
     * @param int $company_id Company ID
     * @return bool True if locked, false otherwise
     */
    public static function is_sync_locked($company_id) {
        global $CFG;
        
        $lock_file = self::get_lock_file_path($company_id);
        
        if (!file_exists($lock_file)) {
            return false;
        }
        
        // Check if lock is expired
        $lock_time = filemtime($lock_file);
        if (time() - $lock_time > self::LOCK_TIMEOUT) {
            // Remove expired lock
            self::release_sync_lock($company_id);
            return false;
        }
        
        return true;
    }

    /**
     * Acquire sync lock for a company
     *
     * @param int $company_id Company ID
     * @param string $operation Operation type (e.g., 'cron', 'rule_change')
     * @return bool True if lock acquired, false otherwise
     */
    public static function acquire_sync_lock($company_id, $operation = 'unknown') {
        global $USER;
        
        if (self::is_sync_locked($company_id)) {
            return false;
        }
        
        $lock_file = self::get_lock_file_path($company_id);
        $lock_data = [
            'company_id' => $company_id,
            'operation' => $operation,
            'user_id' => $USER->id ?? 0,
            'timestamp' => time(),
            'pid' => getmypid()
        ];
        
        $lock_dir = dirname($lock_file);
        if (!is_dir($lock_dir)) {
            mkdir($lock_dir, 0755, true);
        }
        
        return file_put_contents($lock_file, json_encode($lock_data)) !== false;
    }

    /**
     * Release sync lock for a company
     *
     * @param int $company_id Company ID
     * @return bool True if released, false otherwise
     */
    public static function release_sync_lock($company_id) {
        $lock_file = self::get_lock_file_path($company_id);
        
        if (file_exists($lock_file)) {
            return unlink($lock_file);
        }
        
        return true;
    }

    /**
     * Get lock file path for a company
     *
     * @param int $company_id Company ID
     * @return string Lock file path
     */
    private static function get_lock_file_path($company_id) {
        global $CFG;
        
        $company_name = self::get_company_name($company_id);
        $data_dir = $CFG->dataroot . '/local_vanta';
        return $data_dir . '/' . self::LOCK_PREFIX . $company_name . '.lock';
    }

    /**
     * Get JSON data file path for a company
     *
     * @param int $company_id Company ID
     * @return string JSON file path
     */
    private static function get_data_file_path($company_id) {
        global $CFG;
        
        $company_name = self::get_company_name($company_id);
        $data_dir = $CFG->dataroot . '/local_vanta';
        return $data_dir . '/' . self::DATA_FILE_PREFIX . $company_name . '.json';
    }

    /**
     * Get sanitized company name for file naming
     *
     * @param int $company_id Company ID
     * @return string Sanitized company name suitable for file naming
     */
    private static function get_company_name($company_id) {
        global $DB;
        
        try {
            // Get company name from database
            $company_name = $DB->get_field('company', 'name', ['id' => $company_id]);
            
            if (empty($company_name)) {
                // Fallback to company ID if name not found
                return "company_id_$company_id";
            }
            
            // Sanitize company name for file system use
            $sanitized_name = self::sanitize_filename($company_name);
            
            // Add company ID suffix to avoid conflicts if companies have similar names
            return $sanitized_name . "_id_$company_id";
            
        } catch (\Exception $e) {
            // Fallback to company ID if any error occurs
            \vanta_helper::log_sync_attempt(
                0, 0, '', '', 'warning',
                "Could not get company name for ID $company_id, using fallback. Error: " . $e->getMessage(),
                '', ''
            );
            return "company_id_$company_id";
        }
    }

    /**
     * Get company name for display purposes (e.g., in logs)
     *
     * @param int $company_id Company ID
     * @return string Company name for display
     */
    private static function get_company_display_name($company_id) {
        global $DB;
        
        try {
            // Get company name from database
            $company_name = $DB->get_field('company', 'name', ['id' => $company_id]);
            
            if (empty($company_name)) {
                return "Company ID: $company_id";
            }
            
            return "$company_name (ID: $company_id)";
            
        } catch (\Exception $e) {
            return "Company ID: $company_id (lookup failed)";
        }
    }

    /**
     * Sanitize a string for use in filenames
     *
     * @param string $filename Raw filename
     * @return string Sanitized filename
     */
    private static function sanitize_filename($filename) {
        // Remove or replace characters that are not suitable for filenames
        $sanitized = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);
        
        // Replace multiple underscores with single underscore
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        
        // Remove leading/trailing underscores
        $sanitized = trim($sanitized, '_');
        
        // Limit length to 50 characters to avoid path issues
        if (strlen($sanitized) > 50) {
            $sanitized = substr($sanitized, 0, 50);
        }
        
        // Ensure it's not empty
        if (empty($sanitized)) {
            $sanitized = 'unnamed_company';
        }
        
        return strtolower($sanitized);
    }

    /**
     * Store completion data to JSON file
     *
     * @param int $company_id Company ID
     * @param array $completion_data Completion data array
     * @return bool True on success, false on failure
     */
    public static function store_completion_data($company_id, $completion_data) {
        $data_file = self::get_data_file_path($company_id);
        $data_dir = dirname($data_file);
        
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }
        
        $record_count = count($completion_data);
        
        // For large datasets, consider chunking
        if ($record_count > 10000) {
            \vanta_helper::log_sync_attempt(
                0, 0, '', '', 'info',
                "Large dataset detected: $record_count records. Consider implementing chunked storage.",
                '', ''
            );
        }
        
        $json_data = [
            'company_id' => $company_id,
            'generated_at' => time(),
            'completion_count' => $record_count,
            'data' => $completion_data,
            'performance_info' => [
                'record_count' => $record_count,
                'memory_usage' => memory_get_usage(),
                'generation_time' => time()
            ]
        ];
        
        // Use atomic write to prevent corruption
        $temp_file = $data_file . '.tmp';
        
        // For very large datasets, use JSON_UNESCAPED_UNICODE to save space
        $json_flags = JSON_PRETTY_PRINT;
        if ($record_count > 5000) {
            $json_flags = JSON_UNESCAPED_UNICODE; // More compact
        }
        
        $result = file_put_contents($temp_file, json_encode($json_data, $json_flags));
        
        if ($result !== false) {
            $success = rename($temp_file, $data_file);
            
            if ($success) {
                $file_size = round(filesize($data_file) / 1024 / 1024, 2);
                $company_name_for_log = self::get_company_display_name($company_id);
                \vanta_helper::log_sync_attempt(
                    0, 0, '', '', 'info',
                    "JSON file stored for $company_name_for_log: $record_count records, {$file_size}MB",
                    '', ''
                );
            }
            
            return $success;
        }
        
        return false;
    }

    /**
     * Load completion data from JSON file
     *
     * @param int $company_id Company ID
     * @return array|false Completion data array or false on failure
     */
    public static function load_completion_data($company_id) {
        $data_file = self::get_data_file_path($company_id);
        
        if (!file_exists($data_file)) {
            return false;
        }
        
        $json_content = file_get_contents($data_file);
        if ($json_content === false) {
            return false;
        }
        
        $data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        return $data;
    }

    /**
     * Add completion to queue for later processing
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $company_id Company ID
     * @param string $reason Reason for queuing
     * @return bool True on success, false on failure
     */
    public static function queue_completion($userid, $courseid, $company_id, $reason = 'sync_locked') {
        global $DB;
        
        // Check if already queued
        $existing = $DB->get_record(self::QUEUE_TABLE, [
            'userid' => $userid,
            'courseid' => $courseid,
            'company_id' => $company_id,
            'status' => 'pending'
        ]);
        
        if ($existing) {
            // Update existing record
            $existing->queued_at = time();
            $existing->reason = $reason;
            return $DB->update_record(self::QUEUE_TABLE, $existing);
        }
        
        // Create new queue record
        $queue_record = new \stdClass();
        $queue_record->userid = $userid;
        $queue_record->courseid = $courseid;
        $queue_record->company_id = $company_id;
        $queue_record->reason = $reason;
        $queue_record->status = 'pending';
        $queue_record->queued_at = time();
        $queue_record->attempts = 0;
        
        return $DB->insert_record(self::QUEUE_TABLE, $queue_record) !== false;
    }

    /**
     * Process queued completions
     *
     * @param int $company_id Company ID (optional, process all if not specified)
     * @param int $limit Maximum number of items to process
     * @return array Processing results
     */
    public static function process_queue($company_id = null, $limit = 100) {
        global $DB;
        
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Build query conditions
        $conditions = ['status' => 'pending'];
        if ($company_id !== null) {
            $conditions['company_id'] = $company_id;
        }
        
        // Get queued items
        $queued_items = $DB->get_records(
            self::QUEUE_TABLE,
            $conditions,
            'queued_at ASC',
            '*',
            0,
            $limit
        );
        
        foreach ($queued_items as $item) {
            $results['processed']++;
            
            try {
                // Check if sync is still locked
                if (self::is_sync_locked($item->company_id)) {
                    continue; // Skip this item, will be processed later
                }
                
                // Process the completion
                $success = \vanta_helper::handle_course_completion_full_sync(
                    $item->userid,
                    $item->courseid
                );
                
                if ($success) {
                    // Mark as completed
                    $item->status = 'completed';
                    $item->processed_at = time();
                    $DB->update_record(self::QUEUE_TABLE, $item);
                    $results['successful']++;
                } else {
                    // Increment attempts and mark as failed if max attempts reached
                    $item->attempts++;
                    if ($item->attempts >= 3) {
                        $item->status = 'failed';
                        $item->processed_at = time();
                    }
                    $DB->update_record(self::QUEUE_TABLE, $item);
                    $results['failed']++;
                }
                
            } catch (\Exception $e) {
                $item->attempts++;
                $item->error_message = $e->getMessage();
                if ($item->attempts >= 3) {
                    $item->status = 'failed';
                    $item->processed_at = time();
                }
                $DB->update_record(self::QUEUE_TABLE, $item);
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }
        
        return $results;
    }

    /**
     * Regenerate completion data for a company
     *
     * @param int $company_id Company ID
     * @param bool $manage_lock Whether to manage lock acquisition/release (default true)
     * @return bool True on success, false on failure
     */
    public static function regenerate_completion_data($company_id, $manage_lock = true) {
        global $DB;
        
        try {
            // Acquire lock only if we need to manage it
            if ($manage_lock && !self::acquire_sync_lock($company_id, 'data_regeneration')) {
                return false;
            }
            
            // Get sync rule for company
            $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
            if (!$vanta_id) {
                if ($manage_lock) {
                    self::release_sync_lock($company_id);
                }
                return false;
            }
            
            $sync_rule = $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]);
            if (!$sync_rule) {
                if ($manage_lock) {
                    self::release_sync_lock($company_id);
                }
                return false;
            }
            
            // Generate completion data
            $completion_data = self::generate_completion_data($company_id, $sync_rule);
            
            // Store to JSON file
            $result = self::store_completion_data($company_id, $completion_data);
            
            // Release lock only if we managed it
            if ($manage_lock) {
                self::release_sync_lock($company_id);
            }
                
            return $result;
            
        } catch (\Exception $e) {
            if ($manage_lock) {
                self::release_sync_lock($company_id);
            }
            throw $e;
        }
    }

    /**
     * Generate completion data for a company based on sync rules with performance optimizations
     *
     * @param int $company_id Company ID
     * @param \stdClass $sync_rule Sync rule object
     * @return array Completion data array
     */
    private static function generate_completion_data($company_id, $sync_rule) {
        global $DB, $CFG;
        
        $all_rule_courses = array_map('trim', explode(',', $sync_rule->courses));
        $completion_data = [];
        
        // Performance monitoring
        $start_time = microtime(true);
        $memory_start = memory_get_usage();
        $processed_count = 0;
        
        if ($sync_rule->completion_mode === 'any') {
            // ANY mode: Get all completions for any course in the rule with batching
            $completion_data = self::generate_completion_data_batched($company_id, $sync_rule, $all_rule_courses, 'any');
            
        } else if ($sync_rule->completion_mode === 'all') {
            // ALL mode: Only include users who completed ALL courses with batching
            $completion_data = self::generate_completion_data_batched($company_id, $sync_rule, $all_rule_courses, 'all');
        }
        
        // Performance logging
        $end_time = microtime(true);
        $memory_end = memory_get_usage();
        $execution_time = round($end_time - $start_time, 2);
        $memory_used = round(($memory_end - $memory_start) / 1024 / 1024, 2);
        
        \vanta_helper::log_sync_attempt(
            0, 0, '', '', 'info',
            "Data generation performance: {$execution_time}s, {$memory_used}MB, " . count($completion_data) . " records",
            '', ''
        );
        
        return $completion_data;
    }

    /**
     * Generate completion data with batch processing for performance
     *
     * @param int $company_id Company ID
     * @param \stdClass $sync_rule Sync rule object
     * @param array $all_rule_courses Course IDs array
     * @param string $mode Completion mode ('any' or 'all')
     * @return array Completion data array
     */
    private static function generate_completion_data_batched($company_id, $sync_rule, $all_rule_courses, $mode) {
        global $DB;
        
        $completion_data = [];
        $offset = 0;
        $batch_size = self::BATCH_SIZE;
        
        do {
            // Check memory usage
            if ($offset % (self::MEMORY_LIMIT_CHECK * $batch_size) === 0) {
                self::check_memory_usage();
            }
            
            $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
            
            if ($mode === 'any') {
                $sql = "SELECT cs.id, cs.userid, cs.courseid, cs.completion_date,
                               u.email, u.firstname, u.lastname, u.username,
                               c.fullname as course_name, c.timecreated as course_created
                        FROM {local_coursestatus} cs
                        JOIN {user} u ON u.id = cs.userid
                        JOIN {course} c ON c.id = cs.courseid
                        JOIN {company_users} cu ON cu.userid = cs.userid
                        WHERE cs.courseid IN ($placeholders)
                        AND cs.course_status = ?
                        AND cu.companyid = ?
                        ORDER BY cs.userid, cs.courseid
                        LIMIT $batch_size OFFSET $offset";
                
                $params = array_merge($all_rule_courses, ['completed', $company_id]);
                $batch_completions = $DB->get_records_sql($sql, $params);
                
                foreach ($batch_completions as $completion) {
                    $completion_data[] = self::format_completion_record($completion, $sync_rule);
                }
                
            } else if ($mode === 'all') {
                // For 'all' mode, we need a different approach due to complexity
                $batch_completions = self::get_all_mode_completions_batch($company_id, $sync_rule, $all_rule_courses, $offset, $batch_size);
                
                foreach ($batch_completions as $completion) {
                    $completion_data[] = self::format_completion_record($completion, $sync_rule);
                }
            }
            
            $offset += $batch_size;
            
            // Break if no more records
            if (empty($batch_completions) || count($batch_completions) < $batch_size) {
                break;
            }
            
        } while (count($batch_completions) === $batch_size);
        
        return $completion_data;
    }

    /**
     * Get completion data for 'all' mode with batch processing
     *
     * @param int $company_id Company ID
     * @param \stdClass $sync_rule Sync rule object
     * @param array $all_rule_courses Course IDs array
     * @param int $offset Query offset
     * @param int $batch_size Batch size
     * @return array Completion records
     */
    private static function get_all_mode_completions_batch($company_id, $sync_rule, $all_rule_courses, $offset, $batch_size) {
        global $DB;
        
        $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
        $required_course_count = count($all_rule_courses);
        
        // Get users who completed all required courses using a more efficient query
        $sql = "SELECT userid
                FROM (
                    SELECT cs.userid, COUNT(DISTINCT cs.courseid) as completed_courses
                    FROM {local_coursestatus} cs
                    JOIN {company_users} cu ON cu.userid = cs.userid
                    WHERE cs.courseid IN ($placeholders)
                    AND cs.course_status = ?
                    AND cu.companyid = ?
                    GROUP BY cs.userid
                    HAVING COUNT(DISTINCT cs.courseid) >= ?
                ) qualified_users
                LIMIT $batch_size OFFSET $offset";
        
        $params = array_merge($all_rule_courses, ['completed', $company_id, $required_course_count]);
        $qualified_users = $DB->get_fieldset_sql($sql, $params);
        
        if (empty($qualified_users)) {
            return [];
        }
        
        // Get all completions for qualified users
        $user_placeholders = str_repeat('?,', count($qualified_users) - 1) . '?';
        $course_placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
        
        $completion_sql = "SELECT cs.id, cs.userid, cs.courseid, cs.completion_date,
                                  u.email, u.firstname, u.lastname, u.username,
                                  c.fullname as course_name, c.timecreated as course_created
                           FROM {local_coursestatus} cs
                           JOIN {user} u ON u.id = cs.userid
                           JOIN {course} c ON c.id = cs.courseid
                           WHERE cs.userid IN ($user_placeholders)
                           AND cs.courseid IN ($course_placeholders)
                           AND cs.course_status = ?
                           ORDER BY cs.userid, cs.courseid";
        
        $completion_params = array_merge($qualified_users, $all_rule_courses, ['completed']);
        return $DB->get_records_sql($completion_sql, $completion_params);
    }

    /**
     * Check memory usage and take action if approaching limits
     */
    private static function check_memory_usage() {
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = self::convert_to_bytes($memory_limit);
        $current_usage = memory_get_usage();
        
        $usage_percentage = $current_usage / $memory_limit_bytes;
        
        if ($usage_percentage > self::MAX_MEMORY_USAGE) {
            // Log warning
            \vanta_helper::log_sync_attempt(
                0, 0, '', '', 'warning',
                "High memory usage detected: " . round($usage_percentage * 100, 1) . "% of limit",
                '', ''
            );
            
            // Force garbage collection
            gc_collect_cycles();
            
            // If still high, consider breaking
            $new_usage = memory_get_usage();
            if ($new_usage / $memory_limit_bytes > self::MAX_MEMORY_USAGE) {
                throw new \Exception("Memory usage too high: approaching limit of $memory_limit");
            }
        }
    }

    /**
     * Convert memory limit string to bytes
     *
     * @param string $memory_limit Memory limit string (e.g., '128M', '1G')
     * @return int Memory limit in bytes
     */
    private static function convert_to_bytes($memory_limit) {
        $memory_limit = trim($memory_limit);
        $last = strtolower($memory_limit[strlen($memory_limit) - 1]);
        $number = (int) $memory_limit;
        
        switch ($last) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }
        
        return $number;
    }

    /**
     * Format completion record for Vanta API
     *
     * @param \stdClass $completion Completion record from database
     * @param \stdClass $sync_rule Sync rule object
     * @return array Formatted completion data
     */
    private static function format_completion_record($completion, $sync_rule) {
        global $CFG, $DB;
        
        $unique_id = 'user' . $completion->userid . '_course' . $completion->courseid;
        $training_id = 'course_' . $completion->courseid;
        $course_url = $CFG->wwwroot . '/course/view.php?id=' . $completion->courseid;
        
        // Format dates in ISO 8601 format
        $created_date = date('c', $completion->course_created);
        $completed_date = date('c', $completion->completion_date);
        
        // Try to get due date from custom table
        $duedate = $DB->get_field('slms_developer_course', 'duedate', ['id' => $completion->courseid]);
        
        // Due date - if not available, set to 30 days after creation
        $due_date = !empty($duedate) && is_numeric($duedate) 
            ? date('c', $duedate) 
            : date('c', $completion->course_created + (30 * 86400));
        
        // Get frameworks if available
        $frameworks_fulfilled = [];
        if (!empty($sync_rule->frameworks) && is_string($sync_rule->frameworks)) {
            $frameworks_fulfilled = explode(',', $sync_rule->frameworks);
            // Filter out any empty values
            $frameworks_fulfilled = array_filter($frameworks_fulfilled, function($value) {
                return !empty(trim($value));
            });
            
            // If we end up with no valid frameworks, use default
            if (empty($frameworks_fulfilled)) {
                $frameworks_fulfilled = ['SOC2'];
            }
        } else {
            // Default to SOC2 if no frameworks specified
            $frameworks_fulfilled = ['SOC2'];
        }
        
        // Get user's full name using Moodle's fullname function
        $user_fullname = fullname($completion);
        
        return [
            "displayName" => $completion->course_name,
            "uniqueId" => $unique_id,
            "externalUrl" => $course_url,
            "trainingId" => $training_id,
            "trainingName" => $completion->course_name,
            "frameworksFulfilled" => $frameworks_fulfilled,
            "traineeFullName" => $user_fullname,
            "traineeAccountName" => $completion->username ?? 'user' . $completion->userid,
            "traineeEmail" => $completion->email,
            "status" => "COMPLETE",
            "trainingCreatedTimestamp" => $created_date,
            "trainingDueTimestamp" => $due_date,
            "trainingCompletedTimestamp" => $completed_date
        ];
    }

    /**
     * Clean up old queue records
     *
     * @param int $days_old Number of days old to consider for cleanup
     * @return int Number of records cleaned up
     */
    public static function cleanup_queue($days_old = 30) {
        global $DB;
        
        $cutoff_time = time() - ($days_old * 86400);
        
        return $DB->delete_records_select(
            self::QUEUE_TABLE,
            'status IN (?, ?) AND processed_at < ?',
            ['completed', 'failed', $cutoff_time]
        );
    }

    /**
     * Get queue statistics
     *
     * @param int $company_id Company ID (optional)
     * @return array Queue statistics
     */
    public static function get_queue_stats($company_id = null) {
        global $DB;
        
        $conditions = [];
        $params = [];
        
        if ($company_id !== null) {
            $conditions[] = 'company_id = ?';
            $params[] = $company_id;
        }
        
        $where_clause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        $stats = [];
        $statuses = ['pending', 'completed', 'failed'];
        
        foreach ($statuses as $status) {
            $sql = "SELECT COUNT(*) FROM {" . self::QUEUE_TABLE . "} $where_clause";
            if (!empty($where_clause)) {
                $sql .= " AND status = ?";
                $count_params = array_merge($params, [$status]);
            } else {
                $sql .= " WHERE status = ?";
                $count_params = [$status];
            }
            
            $stats[$status] = $DB->count_records_sql($sql, $count_params);
        }
        
        return $stats;
    }
} 