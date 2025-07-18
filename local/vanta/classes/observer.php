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
 * Event observer for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vanta;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');

/**
 * Event observer class for handling course completion events.
 */
class observer {

    /**
     * Observe course completion events and handle sync with locking mechanism.
     * If sync is locked, queue the completion for later processing.
     * If pending regeneration exists, process it first before individual completion.
     *
     * @param \core\event\course_completed $event The course completion event
     * @return bool Success status
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $CFG, $DB;
        
        // Get event data
        $data = $event->get_data();
        $userid = $data['relateduserid'];
        $courseid = $data['courseid'];

        try {
            // Get company ID for the user
            $company_id = self::get_user_company_id($userid);
            if (!$company_id) {
                \vanta_helper::log_sync_attempt(
                    $userid, 
                    $courseid, 
                    '', 
                    '', 
                    'error', 
                    'User not associated with any company',
                    '',
                    ''
                );
                return false;
            }

            // Check if sync is currently locked for this company
            if (sync_manager::is_sync_locked($company_id)) {
                // Sync is locked, queue this completion for later processing
                $queued = sync_manager::queue_completion(
                    $userid, 
                    $courseid, 
                    $company_id, 
                    'sync_locked_during_completion'
                );
                
                if ($queued) {
                    \vanta_helper::log_sync_attempt(
                        $userid, 
                        $courseid, 
                        '', 
                        '', 
                        'queued', 
                        'Completion queued due to sync lock',
                        '',
                        ''
                    );
                    
                    if (debugging()) {
                        mtrace("Queued completion for user $userid, course $courseid due to sync lock");
                    }
                    
                    return true; // Successfully queued
                } else {
                    \vanta_helper::log_sync_attempt(
                        $userid, 
                        $courseid, 
                        '', 
                        '', 
                        'error', 
                        'Failed to queue completion during sync lock',
                        '',
                        ''
                    );
                    return false;
                }
            }

            // **OPTION B FIX**: Check for pending regeneration first
            $pending_regeneration = $DB->get_record('local_vanta_regeneration_queue', [
                'company_id' => $company_id,
                'status' => 'pending'
            ]);

            if ($pending_regeneration) {
                // Skip individual completion - let cron handle the regeneration
                \vanta_helper::log_sync_attempt(
                    $userid, 
                    $courseid, 
                    '', 
                    '', 
                    'info', 
                    'Found pending regeneration in queue, skipping individual completion (will be handled by cron)',
                    '',
                    ''
                );
                
                if (debugging()) {
                    mtrace("Skipped individual completion for user $userid, course $courseid - pending regeneration will be handled by cron for company $company_id");
                }
                
                return true; // Skip individual processing, let cron handle regeneration
            }

            // No pending regeneration or regeneration failed, proceed with individual sync
            $result = \vanta_helper::handle_course_completion_full_sync($userid, $courseid);
            
            if (debugging()) {
                if ($result) {
                    mtrace("Successfully completed full sync to Vanta for course $courseid (triggered by user $userid)");
                } else {
                    mtrace("Failed to complete full sync to Vanta for course $courseid (triggered by user $userid)");
                }
            }

            return $result;
            
        } catch (\Exception $e) {
            if (debugging()) {
                mtrace("Exception in Vanta observer for course $courseid, user $userid: " . $e->getMessage());
            }
            
            // Log the exception
            \vanta_helper::log_sync_attempt(
                $userid, 
                $courseid, 
                '', 
                '', 
                'error', 
                'Observer exception: ' . $e->getMessage(),
                '',
                ''
            );
            
            return false;
        }
    }

    /**
     * Get company ID for a user
     *
     * @param int $userid User ID
     * @return int|false Company ID or false if not found
     */
    private static function get_user_company_id($userid) {
        global $DB;
        
        // First try to get from session if available
        if (isset($_SESSION['USER']) && isset($_SESSION['USER']->companyid)) {
            return $_SESSION['USER']->companyid;
        }
        
        // Fallback to database lookup
        $company_id = $DB->get_field_sql(
            "SELECT cu.companyid 
             FROM {company_users} cu 
             WHERE cu.userid = ?", 
            [$userid]
        );
        
        return $company_id ? (int)$company_id : false;
    }
} 