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
 * Language strings for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Vanta Integration';
$string['settings'] = 'Vanta Integration Settings';
$string['configheading'] = 'Vanta Integration Configuration';
$string['config_info'] = 'This plugin integrates with the Vanta compliance platform to send training completion data and sync user information.';
$string['logs'] = 'Integration Logs';

// Form fields
$string['credentials'] = 'Vanta API Credentials';
$string['client_id'] = 'Vanta Client ID';
$string['client_id_help'] = 'Enter your Vanta OAuth Client ID obtained from the Vanta dashboard.';
$string['client_secret'] = 'Vanta Client Secret';
$string['client_secret_help'] = 'Enter your Vanta OAuth Client Secret obtained from the Vanta dashboard. This is used for API authentication.';
$string['resource_id'] = 'User Security Training Resource ID';
$string['resource_id_help'] = 'Enter the Vanta Resource ID used for training status updates.';

// Integration settings
$string['integration_settings'] = 'Integration Settings';
$string['selected_courses'] = 'Selected Courses';
$string['selected_courses_help'] = 'Enter a comma-separated list of course IDs that should trigger Vanta integration when completed.';
$string['integration_mode'] = 'Integration Mode';
$string['integration_mode_help'] = 'Choose whether completion of any selected course or all selected courses should trigger the integration.';
$string['integration_mode_any'] = 'Any selected course (send data when any selected course is completed)';
$string['integration_mode_all'] = 'All selected courses (only send data when all selected courses are completed)';

// Messages
$string['settingssaved'] = 'Vanta integration settings saved successfully.';
$string['saving'] = 'Saving settings...';
$string['error_saving'] = 'Error saving settings.';
$string['permissions'] = 'You do not have the required permissions to view this page.'; 
$string['scope'] = 'Scope';
$string["scope_value"] = "connectors.self:write-resource";
$string["grant_type"] = "Grant Type";
$string["grant_type_value"] = "client_credentials";
$string['training_sync_rules'] = 'Training Sync Rules';

// API and Web Services
$string['save_success'] = 'API credentials saved successfully';
$string['update_success'] = 'API credentials updated successfully';
$string['save_credentials'] = 'Save Credentials';
$string['update_credentials'] = 'Update Credentials';
$string['some_thing_went_wrong'] = 'Something went wrong. Please try again.';
$string['credentials_form_title'] = 'Vanta API Credentials';
$string['ws_save_credentials'] = 'Save Vanta API credentials';
$string['ws_update_credentials'] = 'Update existing Vanta API credentials';
$string['company_id'] = 'Company ID';
$string['company_id_help'] = 'Enter the company ID for multi-tenancy support, or leave as 0 for system-wide settings.';
$string['status'] = 'Status';
$string['status_help'] = 'Enable or disable these credentials.';
$string['status_enabled'] = 'Enabled';
$string['status_disabled'] = 'Disabled';
$string['client_id_error'] = 'Client ID is required';
$string['client_secret_error'] = 'Client Secret is required';
$string['resource_id_error'] = 'Resource ID is required';
$string['name'] = 'Name';

// Completion Sync Rules strings
$string['frameworks'] = 'Compliance Frameworks';
$string['frameworks_help'] = 'Select the compliance frameworks to which this integration should apply.';
$string['courses'] = 'Training Courses';
$string['courses_help'] = 'Select the courses that should be synchronized with Vanta when completed.';
$string['completion_mode'] = 'Completion Mode';
$string['completion_mode_help'] = 'Choose whether completion of any selected course or all selected courses should trigger the integration.';
$string['completion_mode_any'] = 'Any course (sync when any selected course is completed)';
$string['completion_mode_all'] = 'All courses (sync only when all selected courses are completed)';
$string['save_sync_rules'] = 'Save Sync Rules';
$string['sync_rules_saved'] = 'Completion sync rules saved successfully.';
$string['frameworks_placeholder'] = 'Select compliance frameworks';
$string['courses_placeholder'] = 'Select courses to sync';
$string['ws_save_sync_rules'] = 'Save Vanta completion sync rules';

// Logs page strings
$string['id'] = 'ID';
$string['useremail'] = 'User Email';
$string['coursename'] = 'Course Name';
$string['syncedon'] = 'Synced On';
$string['status'] = 'Status';
$string['actions'] = 'Actions';
$string['filter_logs'] = 'Filter Logs';
$string['course'] = 'Course';
$string['all_courses'] = 'All Courses';
$string['all'] = 'All';
$string['success'] = 'Success';
$string['error'] = 'Error';
$string['info'] = 'Info';
$string['skipped'] = 'Skipped';
$string['from_date'] = 'From Date';
$string['to_date'] = 'To Date';
$string['filter'] = 'Filter';
$string['reset'] = 'Reset';
$string['view_details'] = 'View Details';
$string['no_logs_found'] = 'No logs found matching your filter criteria.';
$string['showing_entries'] = 'Showing entries';
$string['of'] = 'of';
$string['log_details'] = 'Log Details';
$string['general_info'] = 'General Information';
$string['user'] = 'User';
$string['error_message'] = 'Error Message';
$string['request_payload'] = 'Request Payload';
$string['response_payload'] = 'Response Payload';
$string['back_to_logs'] = 'Back to Logs';
$string['frameworks_error'] = 'Please select at least one framework';
$string['courses_error'] = 'Please select at least one course';
$string['completion_mode_error'] = 'Please select a completion mode';
$string['enter_email'] = 'Enter email';
$string['user_email'] = 'User Email';
$string['course_name'] = 'Course Name';
$string['sync_date'] = 'Sync Date';
$string['synced_on'] = 'Synced On';
$string['sync_status'] = 'Sync Status';
$string['sync_error'] = 'Sync Error';
$string['sync_skipped'] = 'Sync Skipped';
$string['sync_success'] = 'Sync Success';
$string['sync_error_message'] = 'Sync Error Message';
$string['sync_request_payload'] = 'Sync Request Payload';
$string['sync_response_payload'] = 'Sync Response Payload';
$string['apply_filters'] = 'Apply Filters';
$string["reset_filters"] = "Reset Filters";
$string["all_statuses"] = "All Statuses";
$string["showing_records"] = "Showing records";
$string['show_password'] = 'Show password';
$string['hide_password'] = 'Hide password';
$string['name_error'] = 'Name is required';
$string['loading'] = 'Loading...';

// Sync Manager strings
$string['sync_task'] = 'Vanta Sync Task';
$string['sync_locked'] = 'Sync Locked';
$string['sync_unlocked'] = 'Sync Unlocked';
$string['queued'] = 'Queued';
$string['processing'] = 'Processing';
$string['completed'] = 'Completed';
$string['failed'] = 'Failed';
$string['pending'] = 'Pending';

// Queue Management strings
$string['queue_stats'] = 'Queue Statistics';
$string['pending_items'] = 'Pending Items';
$string['completed_items'] = 'Completed Items';
$string['failed_items'] = 'Failed Items';
$string['queue_management'] = 'Queue Management';
$string['process_queue'] = 'Process Queue';
$string['clear_queue'] = 'Clear Queue';
$string['queue_processed'] = 'Queue processed successfully';
$string['queue_cleared'] = 'Queue cleared successfully';

// Data Regeneration strings
$string['data_regeneration'] = 'Data Regeneration';
$string['regeneration_status'] = 'Regeneration Status';
$string['regeneration_pending'] = 'Regeneration Pending';
$string['regeneration_processing'] = 'Regeneration Processing';
$string['regeneration_completed'] = 'Regeneration Completed';
$string['regeneration_failed'] = 'Regeneration Failed';
$string['trigger_regeneration'] = 'Trigger Regeneration';
$string['regeneration_triggered'] = 'Data regeneration triggered successfully';
$string['regeneration_failed_msg'] = 'Data regeneration failed';

// Rule Change strings
$string['rule_change'] = 'Rule Change';
$string['rule_creation'] = 'Rule Creation';
$string['rule_deletion'] = 'Rule Deletion';
$string['manual_trigger'] = 'Manual Trigger';
$string['rule_affects_data'] = 'This rule change will trigger data regeneration';
$string['rule_no_effect'] = 'This rule change will not affect existing data';
$string['sync_timing_notice'] = 'Please note: Changes to sync rules may take up to 5 minutes to take effect. The system will automatically regenerate and sync data when the next scheduled task runs.';

// Lock Management strings
$string['lock_acquired'] = 'Sync lock acquired';
$string['lock_released'] = 'Sync lock released';
$string['lock_failed'] = 'Failed to acquire sync lock';
$string['lock_timeout'] = 'Sync lock timeout';
$string['lock_status'] = 'Lock Status';
$string['lock_info'] = 'Lock Information';
$string['lock_operation'] = 'Lock Operation';
$string['lock_timestamp'] = 'Lock Timestamp';
$string['lock_user'] = 'Lock User';

// Performance strings
$string['batch_size'] = 'Batch Size';
$string['processing_batch'] = 'Processing batch {$a->current} of {$a->total}';
$string['records_processed'] = '{$a} records processed';
$string['performance_stats'] = 'Performance Statistics';
$string['memory_usage'] = 'Memory Usage';
$string['execution_time'] = 'Execution Time';

// Error messages
$string['sync_locked_error'] = 'Sync is currently locked. Please try again later.';
$string['queue_full_error'] = 'Queue is full. Please try again later.';
$string['regeneration_in_progress'] = 'Data regeneration is already in progress.';
$string['no_company_found'] = 'No company found for user.';
$string['invalid_rule_data'] = 'Invalid rule data provided.';
$string['rule_not_found'] = 'Training sync rule not found.';
$string['credentials_not_found'] = 'Vanta credentials not found for company.';
$string['training_sync_rules_description'] = 'Configure which frameworks and courses should be synchronized to Vanta.';
$string['frameworks_description'] = 'Select the compliance frameworks to which this integration should apply.';
$string['courses_description'] = 'Select the courses that should be synchronized with Vanta when completed.';
$string['completion_mode_description'] = 'Choose whether completion of any selected course or all selected courses should trigger the integration.';
$string['any_course_completion'] = 'Any course completion';
$string['all_courses_must_be_completed'] = 'All courses must be completed';
$string['PCI'] = 'PCI';
$string['GDPR'] = 'GDPR';
$string['CCPA'] = 'CCPA';
$string['HIPAA'] = 'HIPAA';
$string['ISO27001'] = 'ISO 27001';
$string['SOC2'] = 'SOC 2';

// Date validation
$string['date_validation_error'] = 'To date cannot be earlier than from date.';
$string['training'] = 'Training';
$string['all_training'] = 'All Training';
$string['user_email'] = 'User Email';
$string['enter_email'] = 'Enter email';
$string['all_statuses'] = 'All Statuses';
$string['all_courses'] = 'All Courses';

