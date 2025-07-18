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
 * External functions for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->libdir . '/weblib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use moodle_url;

/**
 * Class containing external functions for local_vanta
 */
class local_vanta_external extends external_api {
    
    /**
     * Returns description of save_credentials method parameters
     *
     * @return external_function_parameters
     */
    public static function save_credentials_parameters() {
        return new external_function_parameters([
            'name' => new external_value(PARAM_TEXT, 'Name'),
            'clientid' => new external_value(PARAM_TEXT, 'Vanta Client ID'),
            'clientsecret' => new external_value(PARAM_TEXT, 'Vanta Client Secret'),
            'scope' => new external_value(PARAM_TEXT, 'API Scope'),
            'granttype' => new external_value(PARAM_TEXT, 'Grant Type'),
        ]);
    }

    /**
     * Save Vanta API credentials
     *
     * @param string $client_id Vanta Client ID
     * @param string $client_secret Vanta Client Secret
     * @param string $resource_id Vanta Resource ID
     * @param int $company_id Company ID
     * @param string $scope API Scope
     * @param string $grant_type Grant Type
     * @param int $status Status
     * @return array Operation result
     */
    public static function save_credentials($name, $clientid, $clientsecret, $scope, $granttype) {
        global $USER, $DB;
        
        $params = self::validate_parameters(self::save_credentials_parameters(), [
            'name' => $name,
            'clientid' => $clientid,
            'clientsecret' => $clientsecret,
            'scope' => $scope,
            'granttype' => $granttype
        ]);

        // // Security check - use \context_system for Moodle's context system
        // require_once($CFG->dirroot . '/lib/accesslib.php');
        // $context = \context_system::instance();
        // self::validate_context($context);
        // require_capability('local/vanta:manage', $context);
        
        // Prepare data object
        $data = new \stdClass();
        $data->name = $params['name'];
        $data->clientid = $params['clientid'];
        $data->clientsecret = $params['clientsecret'];
        $data->scope = $params['scope'];
        $data->granttype = $params['granttype'];
        $data->status = $params['status'];
        $data->companyid = $_SESSION['USER']->companyid ?? 0;

        // check companyid is already exists update if not exists insert
        $existing = $DB->get_record('local_vanta_api_credentials', ['company_id' => $_SESSION['USER']->companyid]);
        if ($existing) {
            $data->id = $existing->id;
            $result = local_vanta_update_api_credentials($data, $USER->id);
        }  else {
            // Insert the record
            $result = local_vanta_save_api_credentials($data, $USER->id);
        }
        
        return [
            'success' => !empty($result),
            'id' => $result ?: 0,
            'message' => !empty($result) ? 'API credentials saved successfully' : 'Failed to save API credentials'
        ];
    }

    /**
     * Returns description of save_credentials method result value
     *
     * @return external_description
     */
    public static function save_credentials_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Operation success status'),
            'id' => new external_value(PARAM_TEXT, 'ID of the created record'),
            'message' => new external_value(PARAM_TEXT, 'Operation result message')
        ]);
    }
    
    /**
     * Returns description of update_credentials method parameters
     *
     * @return external_function_parameters
     */
    public static function update_credentials_parameters() {
        return new external_function_parameters([
            'name' => new external_value(PARAM_TEXT, 'Name'),
            'clientid' => new external_value(PARAM_TEXT, 'Vanta Client ID'),
            'clientsecret' => new external_value(PARAM_TEXT, 'Vanta Client Secret'),
        ]);
    }

    /**
     * Update existing Vanta API credentials
     *
     * @param int $id Record ID
     * @param string $client_id Vanta Client ID
     * @param string $client_secret Vanta Client Secret
     * @param string $resource_id Vanta Resource ID
     * @param int $company_id Company ID
     * @param string $scope API Scope
     * @param string $grant_type Grant Type
     * @param int $status Status
     * @return array Operation result
     */
    public static function update_credentials($name, $clientid, $clientsecret) {
        global $USER, $CFG, $DB;
        
        $params = self::validate_parameters(self::update_credentials_parameters(), [
            'name' => $name,
            'clientid' => $clientid,
            'clientsecret' => $clientsecret,
        ]);
        
        // Security check - use \context_system for Moodle's context system
        // require_once($CFG->dirroot . '/lib/accesslib.php');
        // $context = \context_system::instance();
        // self::validate_context($context);
        // require_capability('local/vanta:manage', $context);
        
        // Prepare data object
        $data = new \stdClass();
        $id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $_SESSION['USER']->companyid]);
        $data->id = $id;
        $data->name = $params['name'];
        $data->clientid = $params['clientid'];
        $data->clientsecret = $params['clientsecret'];
        $data->companyid = $_SESSION['USER']->companyid ?? 0;
        
        // Get the current resource_id to preserve it
        $current_record = $DB->get_record('local_vanta_api_credentials', ['id' => $id]);
        if ($current_record) {
            $data->resourceid = $current_record->resource_id;
        }
        
        // Update the record
        $result = local_vanta_update_api_credentials($data, $USER->id);
        
        return [
            'success' => !empty($result),
            'id' => $result ? $id : 0,
            'message' => !empty($result) ? 'API credentials updated successfully' : 'Failed to update API credentials'
        ];
    }

    /**
     * Returns description of update_credentials method result value
     *
     * @return external_description
     */
    public static function update_credentials_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Operation success status'),
            'id' => new external_value(PARAM_TEXT, 'ID of the updated record'),
            'message' => new external_value(PARAM_TEXT, 'Operation result message')
        ]);
    }
    
    /**
     * Returns description of save_sync_rules method parameters
     *
     * @return external_function_parameters
     */
    public static function save_sync_rules_parameters() {
        return new external_function_parameters([
            'frameworks' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Framework ID'),
                'Array of selected framework IDs',
                VALUE_DEFAULT,
                []
            ),
            'courses' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course ID'),
                'Array of selected course IDs',
                VALUE_DEFAULT,
                []
            ),
            'completionmode' => new external_value(
                PARAM_TEXT,
                'Completion mode (all or any)',
                VALUE_DEFAULT,
                'any'
            ),
            'resourceid' => new external_value(
                PARAM_TEXT,
                'Resource ID',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    /**
     * Save Vanta completion sync rules
     *
     * @param array $frameworks Array of selected framework IDs
     * @param array $courses Array of selected course IDs
     * @param string $completion_mode Completion mode (all or any)
     * @param string $sesskey Session key
     * @return array Operation result
     */
    public static function save_sync_rules($frameworks, $courses, $completionmode, $resourceid) {
        global $USER, $DB, $CFG;
        
        // Parameter validation
        $params = self::validate_parameters(self::save_sync_rules_parameters(), [
            'frameworks' => $frameworks,
            'courses' => $courses,
            'completionmode' => $completionmode,
            'resourceid' => $resourceid,
        ]);
        
        // Security check
        // require_once($CFG->dirroot . '/lib/accesslib.php');
        // $context = \context_system::instance();
        // self::validate_context($context);
        // require_capability('local/vanta:manage', $context);
        
        try {
            // Get existing credentials to obtain resource_id
            $credentials = local_vanta_get_api_credentials($_SESSION['USER']->companyid ?? 0);
            $resource_id = $credentials ? $credentials->resource_id : '';

            $company_id = $_SESSION['USER']->companyid ?? 0;

            $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
            
            // Check if a record already exists
            $existing = $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]);    
            
            // Prepare rule data for rule_manager - convert arrays to comma-separated strings
            $rule_data = [
                'frameworks' => is_array($params['frameworks']) ? implode(',', $params['frameworks']) : $params['frameworks'],
                'courses' => is_array($params['courses']) ? implode(',', $params['courses']) : $params['courses'],
                'completion_mode' => $params['completionmode'],
                'resource_id' => $params['resourceid']
            ];
            
            if ($existing) {
                // Update existing record using rule_manager
                $result = \local_vanta\rule_manager::update_rule($existing->id, $rule_data, $company_id);
                $rule_id = $result ? $existing->id : false;
            } else {
                // Insert new record using rule_manager
                $rule_data['vanta_id'] = $vanta_id;
                $rule_id = \local_vanta\rule_manager::create_rule($rule_data, $company_id);
                $result = $rule_id !== false;
            }
            
            $id = $result ? jwtsecure::Encode($rule_id) : 0;
            return [
                'success' => !empty($result),
                'id' => $id,
                'message' => !empty($result) ? get_string('sync_rules_saved', 'local_vanta') : 'Failed to save sync rules'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Returns description of save_sync_rules method result value
     *
     * @return external_description
     */
    public static function save_sync_rules_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Operation success status'),
            'id' => new external_value(PARAM_TEXT, 'ID of the created record'),
            'message' => new external_value(PARAM_TEXT, 'Operation result message')
        ]);
    }
    
    /**
     * Returns description of get_sync_rules method parameters
     *
     * @return external_function_parameters
     */
    public static function get_sync_rules_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Rule ID', VALUE_DEFAULT, 0)
        ]);
    }
    
    /**
     * Get Vanta training sync rules
     *
     * @param int $id Optional rule ID to retrieve a specific record
     * @return array Operation result with sync rules data
     */
    public static function get_sync_rules($id = 0) {
        global $CFG;
        
        $params = self::validate_parameters(self::get_sync_rules_parameters(), [
            'id' => $id
        ]);
        
        // Security check
        // require_once($CFG->dirroot . '/lib/accesslib.php');
        // $context = \context_system::instance();
        // self::validate_context($context);
        // require_capability('local/vanta:manage', $context);
        
        try {
            if (!empty($params['id'])) {
                $rule = local_vanta_get_training_sync_rules($params['id']);
                
                if (!$rule) {
                    return [
                        'success' => false,
                        'message' => 'Rule not found',
                        'data' => []
                    ];
                }
                
                // Convert string representations to arrays
                $frameworks = !empty($rule->frameworks) ? explode(',', $rule->frameworks) : [];
                $courses = !empty($rule->courses) ? explode(',', $rule->courses) : [];
                
                return [
                    'success' => true,
                    'message' => '',
                    'data' => [
                        'id' => $rule->id,
                        'vanta_id' => $rule->vanta_id,
                        'resource_id' => $rule->resource_id,
                        'frameworks' => $frameworks,
                        'courses' => $courses,
                        'completion_mode' => $rule->completion_mode
                    ]
                ];
            } else {
                // Get all rules
                $rules = local_vanta_get_training_sync_rules();
                $data = [];
                
                foreach ($rules as $rule) {
                    // Convert string representations to arrays
                    $frameworks = !empty($rule->frameworks) ? explode(',', $rule->frameworks) : [];
                    $courses = !empty($rule->courses) ? explode(',', $rule->courses) : [];
                    
                    $data[] = [
                        'id' => $rule->id,
                        'vanta_id' => $rule->vanta_id,
                        'resource_id' => $rule->resource_id,
                        'frameworks' => $frameworks,
                        'courses' => $courses,
                        'completion_mode' => $rule->completion_mode
                    ];
                }
                
                return [
                    'success' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Returns description of get_sync_rules method result value
     *
     * @return external_description
     */
    public static function get_sync_rules_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Operation success status'),
            'message' => new external_value(PARAM_TEXT, 'Operation result message'),
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Rule ID'),
                    'vanta_id' => new external_value(PARAM_TEXT, 'Vanta ID'),
                    'resource_id' => new external_value(PARAM_TEXT, 'Resource ID'),
                    'frameworks' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'Framework ID')
                    ),
                    'courses' => new external_multiple_structure(
                        new external_value(PARAM_INT, 'Course ID')
                    ),
                    'completion_mode' => new external_value(PARAM_TEXT, 'Completion mode')
                ])
            )
        ]);
    }

    /**
     * Returns description of get_logs parameters
     * @return external_function_parameters
     */
    public static function get_logs_parameters() {
        return new external_function_parameters(
            array(
                'page' => new external_value(PARAM_INT, 'Page number', VALUE_DEFAULT, 0),
                'perpage' => new external_value(PARAM_INT, 'Records per page', VALUE_DEFAULT, 10),
                'filters' => new external_single_structure(
                    array(
                        'useremail' => new external_value(PARAM_TEXT, 'User email filter', VALUE_DEFAULT, ''),
                        'courseid' => new external_value(PARAM_INT, 'Course ID filter', VALUE_DEFAULT, 0),
                        'status' => new external_value(PARAM_TEXT, 'Status filter', VALUE_DEFAULT, ''),
                        'fromdate' => new external_value(PARAM_TEXT, 'From date filter', VALUE_DEFAULT, ''),
                        'todate' => new external_value(PARAM_TEXT, 'To date filter', VALUE_DEFAULT, '')
                    ),
                    'Filter parameters',
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    /**
     * Get filtered logs
     * @param int $page Page number
     * @param int $perpage Records per page
     * @param array $filters Filter parameters
     * @return array Logs and pagination data
     */
    public static function get_logs($page = 0, $perpage = 10, $filters = array()) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_logs_parameters(),
            array(
                'page' => $page,
                'perpage' => $perpage,
                'filters' => $filters
            )
        );

        // Build WHERE clause and parameters for filtering
        $where_conditions = array();
        $sql_params = array();

        if (!empty($filters['useremail'])) {
            $where_conditions[] = "useremail LIKE :useremail";
            $sql_params['useremail'] = '%' . $filters['useremail'] . '%';
        }

        if (!empty($filters['courseid'])) {
            $where_conditions[] = "courseid = :courseid";
            $sql_params['courseid'] = $filters['courseid'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['success', 'error', 'info', 'skipped'])) {
            $where_conditions[] = "status = :status";
            $sql_params['status'] = $filters['status'];
        }

        if (!empty($filters['fromdate'])) {
            $fromdate = strtotime($filters['fromdate'] . ' 00:00:00');
            $where_conditions[] = "syncedon >= :fromdate";
            $sql_params['fromdate'] = $fromdate;
        }

        if (!empty($filters['todate'])) {
            $todate = strtotime($filters['todate'] . ' 23:59:59');
            $where_conditions[] = "syncedon <= :todate";
            $sql_params['todate'] = $todate;
        }

        // Build final WHERE clause
        $where_clause = empty($where_conditions) ? '1=1' : implode(' AND ', $where_conditions);

        // Get total count for pagination
        $total_logs = $DB->count_records_select('local_vanta_sync_logs', $where_clause, $sql_params);

        // Calculate offset
        $offset = $page * $perpage;

        // Get the logs with pagination and filtering
        $logs = $DB->get_records_select(
            'local_vanta_sync_logs',
            $where_clause,
            $sql_params,
            'id DESC',
            '*',
            $offset,
            $perpage
        );

        // Format the log data
        $formatted_logs = array();
        foreach ($logs as $log) {
            $formatted_logs[] = array(
                'id' => $log->id,
                'useremail' => $log->useremail,
                'coursename' => $log->coursename,
                'syncedon' => $log->syncedon,
                'syncedon_formatted' => userdate($log->syncedon, get_string('strftimedatetime', 'langconfig')),
                'status' => $log->status,
                'status_class' => $log->status === 'success' ? 'text-success' : ($log->status === 'error' ? 'text-danger' : 'text-info'),
                'status_success' => $log->status === 'success',
                'status_error' => $log->status === 'error',
                'status_info' => $log->status === 'info',
                'status_skipped' => $log->status === 'skipped',
                'has_error' => !empty($log->error_message),
                'error_message' => $log->error_message,
                'view_url' => (new moodle_url('/local/vanta/view_log.php', ['id' => jwtsecure::encode(['id' => $log->id])]))->out()
            );
        }

        return array(
            'logs' => $formatted_logs,
            'total' => $total_logs,
            'page' => $page,
            'perpage' => $perpage,
            'showing_from' => $offset + 1,
            'showing_to' => min($offset + $perpage, $total_logs)
        );
    }

    /**
     * Returns description of get_logs return values
     * @return external_single_structure
     */
    public static function get_logs_returns() {
        return new external_single_structure(
            array(
                'logs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Log ID'),
                            'useremail' => new external_value(PARAM_TEXT, 'User email'),
                            'coursename' => new external_value(PARAM_TEXT, 'Course name'),
                            'syncedon' => new external_value(PARAM_INT, 'Sync timestamp'),
                            'syncedon_formatted' => new external_value(PARAM_TEXT, 'Formatted sync date'),
                            'status' => new external_value(PARAM_TEXT, 'Status'),
                            'status_class' => new external_value(PARAM_TEXT, 'Status CSS class'),
                            'status_success' => new external_value(PARAM_BOOL, 'Is success status'),
                            'status_error' => new external_value(PARAM_BOOL, 'Is error status'),
                            'status_info' => new external_value(PARAM_BOOL, 'Is info status'),
                            'status_skipped' => new external_value(PARAM_BOOL, 'Is skipped status'),
                            'has_error' => new external_value(PARAM_BOOL, 'Has error message'),
                            'error_message' => new external_value(PARAM_TEXT, 'Error message'),
                            'view_url' => new external_value(PARAM_TEXT, 'View log URL')
                        )
                    )
                ),
                'total' => new external_value(PARAM_INT, 'Total number of logs'),
                'page' => new external_value(PARAM_INT, 'Current page number'),
                'perpage' => new external_value(PARAM_INT, 'Records per page'),
                'showing_from' => new external_value(PARAM_INT, 'First record number being shown'),
                'showing_to' => new external_value(PARAM_INT, 'Last record number being shown')
            )
        );
    }

    /**
     * Returns description of get_course_options parameters
     * @return external_function_parameters
     */
    public static function get_course_options_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get course options for filter dropdown
     * @return array Course options
     */
    public static function get_course_options() {
        global $DB;

        // Get all courses that have logs, excluding system operations
        $courses = $DB->get_records_sql(
            'SELECT DISTINCT l.courseid, l.coursename 
             FROM {local_vanta_sync_logs} l
             INNER JOIN {course} c ON c.id = l.courseid
             WHERE l.courseid > 0 
               AND l.coursename IS NOT NULL 
               AND l.coursename != ""
               AND c.id != 1
             ORDER BY l.coursename'
        );

        $options = array();
        foreach ($courses as $course) {
            $options[] = array(
                'id' => $course->courseid,
                'name' => $course->coursename
            );
        }

        return array('courses' => $options);
    }

    /**
     * Returns description of get_course_options return values
     * @return external_single_structure
     */
    public static function get_course_options_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Course ID'),
                            'name' => new external_value(PARAM_TEXT, 'Course name')
                        )
                    )
                )
            )
        );
    }
} 