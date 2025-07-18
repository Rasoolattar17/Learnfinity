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
 * Library functions for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use moodle_url;

defined('MOODLE_INTERNAL') || die();

// Include Moodle's curl library
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/classes/generic.php');
// Add required Moodle core libraries for tab navigation
require_once($CFG->libdir . '/outputlib.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->libdir . '/navigationlib.php');

/**
 * Helper class for Vanta API interactions.
 */
class vanta_helper {
    /** @var string Base URL for Vanta API */
    const API_BASE_URL = 'https://app.vanta.com/api';

    /** @var string Token endpoint */
    const TOKEN_ENDPOINT = '/oauth/token';

    /** @var string Training status endpoint */
    const TRAINING_STATUS_ENDPOINT = '/v1/resources/user_security_training_status';

    /** @var string User account endpoint */
    const USER_ACCOUNT_ENDPOINT = '/user/account';

    /** @var int Token expiration time (in seconds) */
    const TOKEN_EXPIRATION = 3600;

    /**
     * Get user display info for logging
     * 
     * @param int $userid User ID
     * @return array Array with username, email, fullname
     */
    private static function get_user_info($userid) {
        global $DB;
        
        if ($userid == 0) {
            return [
                'username' => 'System Operation',
                'email' => 'System Operation', 
                'fullname' => 'System Operation'
            ];
        }
        
        $user = $DB->get_record('user', ['id' => $userid], 'username, email, firstname, lastname');
        if (!$user) {
            return [
                'username' => "User ID: $userid (not found)",
                'email' => "User ID: $userid (not found)",
                'fullname' => "User ID: $userid (not found)"
            ];
        }
        
        return [
            'username' => $user->username,
            'email' => $user->email,
            'fullname' => fullname($user)
        ];
    }
    
    /**
     * Get course display info for logging
     * 
     * @param int $courseid Course ID
     * @return array Array with coursename, shortname
     */
    private static function get_course_info($courseid) {
        global $DB;
        
        if ($courseid == 0) {
            return [
                'coursename' => 'Multiple Courses',
                'shortname' => 'Multiple Courses'
            ];
        }
        
        $course = $DB->get_record('course', ['id' => $courseid], 'fullname, shortname');
        if (!$course) {
            return [
                'coursename' => "Course ID: $courseid (not found)",
                'shortname' => "Course ID: $courseid (not found)"
            ];
        }
        
        return [
            'coursename' => $course->fullname,
            'shortname' => $course->shortname
        ];
    }
    
    /**
     * Get company display info for logging
     * 
     * @param int $company_id Company ID
     * @return array Array with companyname, shortname
     */
    private static function get_company_info($company_id) {
        global $DB;
        
        if ($company_id == 0) {
            return [
                'companyname' => 'System Company',
                'shortname' => 'System Company'
            ];
        }
        
        $company = $DB->get_record('company', ['id' => $company_id], 'name, shortname');
        if (!$company) {
            return [
                'companyname' => "Company ID: $company_id (not found)",
                'shortname' => "Company ID: $company_id (not found)"
            ];
        }
        
        return [
            'companyname' => $company->name,
            'shortname' => $company->shortname
        ];
    }

    /**
     * Get Vanta API access token.
     * Checks cache first, refreshes if expired.
     *
     * @param int|null $company_id Company ID (if null, gets from session)
     * @return string|bool The access token or false on failure
     */
    public static function get_access_token($company_id = null) {
        global $CFG, $DB;
        
        if (debugging()) {
            echo '<div class="alert alert-info">Starting get_access_token()...</div>';
        }
        
        // Get credentials from database - use provided company_id or fallback to session
        if ($company_id === null) {
            $company_id = $_SESSION['USER']->companyid ?? 0;
        }
        
        if (debugging()) {
            echo '<div class="alert alert-info">Looking for credentials with company_id: ' . $company_id . '</div>';
        }
        
        $credentials = local_vanta_get_api_credentials($company_id);
        
        if (!$credentials || empty($credentials->client_id) || empty($credentials->client_secret)) {
            // Log the specific issue for better debugging
            $error_detail = 'No credentials found';
            if (!$credentials) {
                $error_detail = "No credentials record found for company_id: $company_id";
            } else if (empty($credentials->client_id)) {
                $error_detail = "Empty client_id for company_id: $company_id";
            } else if (empty($credentials->client_secret)) {
                $error_detail = "Empty client_secret for company_id: $company_id";
            }
            
            self::log_sync_attempt(0, 0, '', '', 'error', 'Failed to obtain access token: ' . $error_detail, '', '');
            
            if (debugging()) {
                if (!$credentials) {
                    echo '<div class="alert alert-danger">No credentials record found for company_id: ' . $company_id . '</div>';
                } else {
                    echo '<div class="alert alert-danger">Credentials found but missing client_id or client_secret</div>';
                    echo '<div class="alert alert-info">Client ID empty: ' . (empty($credentials->client_id) ? 'YES' : 'NO') . '</div>';
                    echo '<div class="alert alert-info">Client Secret empty: ' . (empty($credentials->client_secret) ? 'YES' : 'NO') . '</div>';
                }
            }
            return false;
        }
        
        if (debugging()) {
            echo '<div class="alert alert-success">Valid credentials found. Client ID: ' . substr($credentials->client_id, 0, 20) . '...</div>';
        }

        // Check for cached token in config
        $token_data = get_config('local_vanta', 'token_cache');
        
        if ($token_data) {
            $token_data = json_decode($token_data);
            // If token exists and is not expired, return it
            if ($token_data && isset($token_data->expires_at) && $token_data->expires_at > time()) {
                if (debugging()) {
                    echo '<div class="alert alert-info">Using cached token (expires: ' . date('Y-m-d H:i:s', $token_data->expires_at) . ')</div>';
                }
                return $token_data->token;
            } else if (debugging()) {
                echo '<div class="alert alert-info">Cached token expired or invalid, fetching new token...</div>';
            }
        } else if (debugging()) {
            echo '<div class="alert alert-info">No cached token found, fetching new token...</div>';
        }

        // Get new token
        if (debugging()) {
            echo '<div class="alert alert-info">Calling fetch_new_token()...</div>';
        }
        
        $token = self::fetch_new_token(
            $credentials->client_id, 
            $credentials->client_secret, 
            $credentials->scope, 
            $credentials->grant_type
        );
        
        if ($token) {
            // Cache the token in config
            $token_data = new \stdClass();
            $token_data->token = $token;
            $token_data->expires_at = time() + self::TOKEN_EXPIRATION;
            set_config('token_cache', json_encode($token_data), 'local_vanta');
            
            if (debugging()) {
                echo '<div class="alert alert-success">Token cached successfully. Expires: ' . date('Y-m-d H:i:s', $token_data->expires_at) . '</div>';
            }
            
            return $token;
        } else {
            if (debugging()) {
                echo '<div class="alert alert-danger">fetch_new_token() returned false</div>';
            }
        }

        // Log failure to obtain new token
        self::log_sync_attempt(0, 0, '', '', 'error', 'Failed to obtain access token: Token fetch returned false', '', '');
        return false;
    }

    /**
     * Fetch new access token from Vanta API.
     *
     * @param string $client_id Vanta client ID
     * @param string $client_secret Vanta client secret
     * @param string $scope API scope
     * @param string $grant_type Grant type
     * @return string|bool The access token or false on failure
     */
    private static function fetch_new_token($client_id, $client_secret, $scope, $grant_type) {
        global $CFG;
        
        try {
            // Initialize curl
            $curl = curl_init();
            
            // Prepare the JSON payload using proper json_encode
            $payload_data = [
                "client_id" => $client_id,
                "client_secret" => $client_secret,
                "scope" => $scope,
                "grant_type" => $grant_type
            ];
            $json_payload = json_encode($payload_data);
 
            // Full URL for the API endpoint
            $full_url = 'https://api.vanta.com/oauth/token';
            
            // Set curl options with production SSL configuration
            curl_setopt_array($curl, array(
                CURLOPT_URL => $full_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json_payload,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                // Production SSL Configuration
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_USERAGENT => 'Moodle-Vanta-Integration/1.0',
            ));
            
            // For debugging
            if (debugging()) {
                echo '<div class="alert alert-info">Vanta API endpoint: ' . $full_url . '</div>';
                echo '<div class="alert alert-info">Vanta API request payload: <pre>' . htmlspecialchars($json_payload) . '</pre></div>';
            }
            
            // Execute the request
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Get HTTP response code for better debugging
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // Check for curl errors
            if (curl_errno($curl)) {
                $error_message = 'Curl error: ' . curl_errno($curl) . ' - ' . curl_error($curl);
                self::log_sync_attempt(0, 0, $json_payload, '', 'error', 'Failed to obtain access token: ' . $error_message, '', '');
                
                if (debugging()) {
                    echo '<div class="alert alert-danger">' . $error_message . '</div>';
                }
                curl_close($curl);
                return false;
            }
            
            // Close curl
            curl_close($curl);
            
            // Check for empty response
            if (empty($response)) {
                $error_message = 'Empty response received from Vanta API (HTTP ' . $http_code . ')';
                self::log_sync_attempt(0, 0, $json_payload, '', 'error', 'Failed to obtain access token: ' . $error_message, '', '');
                
                if (debugging()) {
                    echo '<div class="alert alert-warning">Empty response received from Vanta API (HTTP ' . $http_code . ')</div>';
                }
                return false;
            }
            
            // For debugging
            if (debugging()) {
                echo '<div class="alert alert-info">Vanta API response (HTTP ' . $http_code . '): <pre>';
                echo htmlspecialchars($response);
                echo '</pre></div>';
            }
            
            // Parse the response
            $result = json_decode($response);
            
            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_message = 'JSON decode error: ' . json_last_error_msg() . '. Response: ' . substr($response, 0, 500);
                self::log_sync_attempt(0, 0, $json_payload, $response, 'error', 'Failed to obtain access token: ' . $error_message, '', '');
                
                if (debugging()) {
                    echo '<div class="alert alert-danger">' . $error_message . '</div>';
                }
                return false;
            }
            
            // Check for HTTP errors
            if ($http_code !== 200) {
                $error_message = 'HTTP error ' . $http_code;
                if (is_object($result) && isset($result->error)) {
                    $error_message .= ': ' . $result->error;
                    if (isset($result->error_description)) {
                        $error_message .= ' - ' . $result->error_description;
                    }
                }
                
                self::log_sync_attempt(0, 0, $json_payload, $response, 'error', 'Failed to obtain access token: ' . $error_message, '', '');
                
                if (debugging()) {
                    echo '<div class="alert alert-danger">' . $error_message . '</div>';
                }
                return false;
            }
            
            // Return token if successful
            if (is_object($result) && isset($result->access_token)) {
                if (debugging()) {
                    echo '<div class="alert alert-success">Token retrieved successfully!</div>';
                }
                return $result->access_token;
            }
            
            // Log error details if token retrieval fails
            if (debugging()) {
                echo '<div class="alert alert-danger">Failed to get access token (HTTP ' . $http_code . '). Response: <pre>';
                echo htmlspecialchars($response);
                echo '</pre></div>';
                
                if (is_object($result) && isset($result->error)) {
                    $error_msg = 'Error: ' . $result->error . 
                        (isset($result->error_description) ? ' - ' . $result->error_description : '');
                    echo '<div class="alert alert-danger">' . $error_msg . '</div>';
                }
            }
            
            self::log_sync_attempt(0, 0, $json_payload, $response, 'error', 'Failed to obtain access token: ' . $error_message, '', '');
            
            if (debugging()) {
                echo '<div class="alert alert-danger">Failed to get access token: ' . $error_message . '</div>';
                echo '<div class="alert alert-info">Full response: <pre>' . htmlspecialchars($response) . '</pre></div>';
            }
            
            return false;
        } catch (Exception $e) {
            $error_message = 'Exception in fetch_new_token: ' . $e->getMessage();
            self::log_sync_attempt(0, 0, '', '', 'error', 'Failed to obtain access token: ' . $error_message, '', '');
            
            if (debugging()) {
                echo '<div class="alert alert-danger">' . $error_message . '</div>';
            }
            return false;
        }
    }

    /**
     * Send training completion data to Vanta.
     * Can handle both single completion and multiple completions.
     * Supports two data formats:
     * 1. userid/courseid format: [['userid' => 123, 'courseid' => 456], ...]
     * 2. Vanta API format: [['id' => 'user123_course456', 'trainingId' => 'course_456', ...], ...]
     *
     * @param mixed $userid_or_completions Either a single user ID (for single completion) 
     *                                     or array of completion records (for batch processing)
     * @param int|null $courseid Course ID (only used when sending single completion)
     * @param int|null $company_id Company ID (if null, will be determined from session/user data)
     * @return bool Success status
     */
    public static function send_training_completion($userid_or_completions, $courseid = null, $company_id = null) {
        global $DB, $CFG;

        try {
            // Determine if this is single or batch operation
            $completions = [];
            $is_batch = false;
            $is_vanta_format = false;
            
            if (is_array($userid_or_completions)) {
                // Batch mode: array of completions provided
                $completions = $userid_or_completions;
                $is_batch = true;
                
                if (empty($completions)) {
                    self::log_sync_attempt(0, 0, '', '', 'error', 'No completions provided to send', '', '');
                    return false;
                }
                
                // Detect data format by checking the first record
                $first_record = reset($completions);
                if (isset($first_record['displayName']) && isset($first_record['uniqueId'])) {
                    // This is already in Vanta API format
                    $is_vanta_format = true;
                } else if (isset($first_record['userid']) && isset($first_record['courseid'])) {
                    // This is in userid/courseid format
                    $is_vanta_format = false;
                } else {
                    self::log_sync_attempt(0, 0, '', '', 'error', 'Invalid completion data format', '', '');
                    return false;
                }
                
            } else if (is_numeric($userid_or_completions) && is_numeric($courseid)) {
                // Single mode: userid and courseid provided
                $completions = [['userid' => $userid_or_completions, 'courseid' => $courseid]];
                $is_batch = false;
                $is_vanta_format = false;
            } else {
                // Invalid parameters
                self::log_sync_attempt(0, 0, '', '', 'error', 'Invalid parameters provided', '', '');
                return false;
            }

            // Determine company_id if not provided
            if ($company_id === null) {
                // Try to get from session first (works for web requests)
                $company_id = $_SESSION['USER']->companyid ?? 0;
                
                // If no session company_id (e.g., during cron/scheduled tasks), try to determine from user data
                if (!$company_id && !$is_vanta_format && !empty($completions)) {
                    $first_completion = reset($completions);
                    $first_userid = $first_completion['userid'] ?? 0;
                    if ($first_userid) {
                        $company_id = $DB->get_field_sql(
                            "SELECT cu.companyid FROM {company_users} cu WHERE cu.userid = ?", 
                            [$first_userid]
                        );
                        
                        if ($company_id) {
                            $user_info = self::get_user_info($first_userid);
                            $company_info = self::get_company_info($company_id);
                            self::log_sync_attempt(0, 0, '', '', 'debug', 
                                "Determined company from user data: company '{$company_info['companyname']}' for user '{$user_info['username']}'", '', '');
                        }
                    }
                }
                
                // If still no company_id and this is Vanta format data, we need to extract from existing data
                if (!$company_id && $is_vanta_format && !empty($completions)) {
                    // For Vanta format, try to extract userid from uniqueId and lookup company
                    $first_completion = reset($completions);
                    $unique_id = $first_completion['uniqueId'] ?? '';
                    
                    // uniqueId format is usually 'user{userid}_course{courseid}'
                    if (preg_match('/user(\d+)_course(\d+)/', $unique_id, $matches)) {
                        $extracted_userid = $matches[1];
                        $company_id = $DB->get_field_sql(
                            "SELECT cu.companyid FROM {company_users} cu WHERE cu.userid = ?", 
                            [$extracted_userid]
                        );
                        
                        if ($company_id) {
                            $user_info = self::get_user_info($extracted_userid);
                            $company_info = self::get_company_info($company_id);
                            self::log_sync_attempt(0, 0, '', '', 'debug', 
                                "Determined company from Vanta data: company '{$company_info['companyname']}' for user '{$user_info['username']}'", '', '');
                        }
                    }
                }
                
                // If still no company_id, this is a critical error
                if (!$company_id) {
                    $context = isset($_SESSION['USER']) ? 'web request' : 'scheduled task';
                    $session_info = $_SESSION['USER']->companyid ?? 'not set';
                    self::log_sync_attempt(0, 0, '', '', 'error', 
                        "Could not determine company in $context. Session company: $session_info", '', '');
                    return false;
                }
            }

            // Get access token with the correct company_id
            $token = self::get_access_token($company_id);
        
            if (!$token) {
                // Log error for representative completion
                $first_completion = reset($completions);
                $userid = $is_vanta_format ? 0 : ($first_completion['userid'] ?? 0);
                $courseid = $is_vanta_format ? 0 : ($first_completion['courseid'] ?? 0);
                self::log_sync_attempt($userid, $courseid, '', '', 'error', 'Failed to obtain access token', '', '');
                return false;
            }

            // If data is already in Vanta format, use it directly
            if ($is_vanta_format) {
                // Get resource_id from sync rule
                $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
                if (empty($vanta_id)) {
                    self::log_sync_attempt(0, 0, '', '', 'error', 'No Vanta credentials found for company', '', '');
                    return false;
                }
                
                $rule_id = $DB->get_field('local_vanta_training_sync_rules', 'id', ['vanta_id' => $vanta_id]);
                if (empty($rule_id)) {
                    self::log_sync_attempt(0, 0, '', '', 'error', 'No sync rules found for Vanta credential', '', '');
                    return false;
                }
                
                $rules = local_vanta_get_training_sync_rules($rule_id);
                if (empty($rules) || empty($rules->resource_id)) {
                    self::log_sync_attempt(0, 0, '', '', 'error', 'No resource ID found in rules', '', '');
                    return false;
                }
                
                $resource_id = $rules->resource_id;
                $total_records = count($completions);
                
                // **VANTA API FIX**: Send ALL data at once as Vanta expects complete replacement
                if ($total_records > 10000) {
                    self::log_sync_attempt(0, 0, '', '', 'warning', 
                        "Large dataset detected: $total_records records. Vanta API requires all data in single call - optimizing for performance.", '', '');
                    
                    // Use optimized method for very large datasets
                    return self::send_large_payload_optimized($token, $resource_id, $completions);
                }
                
                // Standard processing for smaller datasets  
                $resources = $completions;
                
                // Prepare the full payload
                $payload = [
                    "resourceId" => $resource_id,
                    "resources" => $resources
                ];
                
                // Convert payload to JSON string
                $request_payload = json_encode($payload);
                
                // Log the operation
                $operation_type = $is_batch ? 'Batch operation for ' . count($resources) . ' completion(s) (Vanta format - complete replacement)' : 'Single completion (Vanta format)';
                
            } else {
                // Original logic for userid/courseid format
                
                // Get Vanta credential ID for this company
                $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
                if (empty($vanta_id)) {
                    $first_completion = reset($completions);
                    $userid = $first_completion['userid'] ?? 0;
                    $courseid = $first_completion['courseid'] ?? 0;
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "No Vanta credentials found for company (user: '{$user_info['username']}', course: '{$course_info['coursename']}')", '', '');
                    return false;
                }
                
                // Get the rule ID for this Vanta credential
                $rule_id = $DB->get_field('local_vanta_training_sync_rules', 'id', ['vanta_id' => $vanta_id]);
                if (empty($rule_id)) {
                    $first_completion = reset($completions);
                    $userid = $first_completion['userid'] ?? 0;
                    $courseid = $first_completion['courseid'] ?? 0;
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "No sync rules found for Vanta credential (user: '{$user_info['username']}', course: '{$course_info['coursename']}')", '', '');
                    return false;
                }
                
                // Get resource_id from training sync rules
                $rules = local_vanta_get_training_sync_rules($rule_id);
                if (empty($rules) || empty($rules->resource_id)) {
                    $first_completion = reset($completions);
                    $userid = $first_completion['userid'] ?? 0;
                    $courseid = $first_completion['courseid'] ?? 0;
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "No resource ID found in rules (user: '{$user_info['username']}', course: '{$course_info['coursename']}')", '', '');
                    return false;
                }
                
                $resource_id = $rules->resource_id;
                
                // Get frameworks if available
                $frameworks_fulfilled = [];
                if (!empty($rules->frameworks) && is_string($rules->frameworks)) {
                    $frameworks_fulfilled = explode(',', $rules->frameworks);
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
                
                // Build resources array for all completions
                $resources = [];
                
                foreach ($completions as $completion_record) {
                    $userid = $completion_record['userid'];
                    $courseid = $completion_record['courseid'];
                    
                    // Skip if course is not in the rule's courses
                    if (!empty($rules->courses)) {
                        $course_ids = explode(',', $rules->courses);
                        if (!in_array($courseid, $course_ids)) {
                            if (!$is_batch) {
                                // For single operations, log this as an error
                                $user_info = self::get_user_info($userid);
                                $course_info = self::get_course_info($courseid);
                                self::log_sync_attempt($userid, $courseid, '', '', 'error', "Course '{$course_info['coursename']}' not in sync rule courses list for user '{$user_info['username']}'", '', '');
                                return false;
                            }
                            continue; // For batch operations, skip this course
                        }
                    }
                    
                    // Get user data
                    $user = $DB->get_record('user', ['id' => $userid], '*');
                    if (empty($user)) {
                        $user_info = self::get_user_info($userid);
                        $course_info = self::get_course_info($courseid);
                        self::log_sync_attempt($userid, $courseid, '', '', 'error', "User '{$user_info['username']}' not found for course '{$course_info['coursename']}'", '', '');
                        if (!$is_batch) return false;
                        continue;
                    }
                    
                    // Get course completion data
                    $completion = $DB->get_record('local_coursestatus', 
                        ['userid' => $userid, 'courseid' => $courseid]);
                    if (empty($completion) || empty($completion->completion_date)) {
                        $course_info = self::get_course_info($courseid);
                        self::log_sync_attempt($userid, $courseid, '', '', 'error', "Course completion data not found or incomplete for course '{$course_info['coursename']}'", $user->email, '');
                        if (!$is_batch) return false;
                        continue;
                    }
                    // Get course data
                    $course = $DB->get_record('course', ['id' => $courseid], '*');
                    if (empty($course)) {
                        $course_info = self::get_course_info($courseid);
                        self::log_sync_attempt($userid, $courseid, '', '', 'error', "Course '{$course_info['coursename']}' not found", $user->email, '');
                        if (!$is_batch) return false;
                        continue;
                    }
                    
                    // Generate unique ID for this user-course combination
                    $unique_id = 'user' . $userid . '_course' . $courseid;
                    $training_id = 'course_' . $courseid;
                    
                    // Get course URL
                    $course_url = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                    
                    // Format dates in ISO 8601 format
                    $created_date = date('c', $course->timecreated);
                    
                    // Try to get due date from custom table
                    $duedate = $DB->get_field('slms_developer_course', 'duedate', ['id' => $courseid]);
                    
                    // Due date - if not available, set to 30 days after creation
                    $due_date = !empty($duedate) && is_numeric($duedate) 
                        ? date('c', $duedate) 
                        : date('c', $course->timecreated + (30 * 86400));
                    
                    // Completion date
                    $completed_date = !empty($completion->completion_date) 
                        ? date('c', $completion->completion_date) 
                        : date('c', time()); // Fallback to current time if missing
                    
                    // Get user's full name
                    $user_fullname = fullname($user);
                    
                    // Add to resources array
                    $resources[] = [
                        "displayName" => $course->fullname,
                        "uniqueId" => $unique_id,
                        "externalUrl" => $course_url,
                        "trainingId" => $training_id,
                        "trainingName" => $course->fullname,
                        "frameworksFulfilled" => $frameworks_fulfilled,
                        "traineeFullName" => $user_fullname,
                        "traineeAccountName" => $user->username,
                        "traineeEmail" => $user->email,
                        "status" => "COMPLETE",
                        "trainingCreatedTimestamp" => $created_date,
                        "trainingDueTimestamp" => $due_date,
                        "trainingCompletedTimestamp" => $completed_date
                    ];
                }
                
                // If no valid resources to send, return false
                if (empty($resources)) {
                    $first_completion = reset($completions);
                    $userid = $first_completion['userid'] ?? 0;
                    $courseid = $first_completion['courseid'] ?? 0;
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    $error_msg = $is_batch ? 'No valid completions to send after processing' : "Course completion could not be processed for user '{$user_info['username']}' and course '{$course_info['coursename']}'";
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', $error_msg, '', '');
                    return false;
                }
                
                // Prepare the full payload
                $payload = [
                    "resourceId" => $resource_id,
                    "resources" => $resources
                ];

                // Convert payload to JSON string
                $request_payload = json_encode($payload);
                
                // Add operation type to log message
                $operation_type = $is_batch ? 'Batch operation for ' . count($resources) . ' completion(s) (userid/courseid format)' : 'Single completion (userid/courseid format)';
            }
            
            // Initialize curl
            $curl = curl_init();
            
            // Set curl options with production SSL configuration
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.vanta.com/v1/resources/user_security_training_status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $request_payload,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
                // Production SSL Configuration
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'Moodle-Vanta-Integration/1.0',
            ));
            
            // Execute the request
            $response = curl_exec($curl);
            // Check for curl errors
            if (curl_errno($curl)) {
                $error_message = 'Curl error: ' . curl_errno($curl) . ' - ' . curl_error($curl);
                $first_completion = reset($completions);
                $userid = $is_vanta_format ? 0 : ($first_completion['userid'] ?? 0);
                $courseid = $is_vanta_format ? 0 : ($first_completion['courseid'] ?? 0);
                $user_info = self::get_user_info($userid);
                $course_info = self::get_course_info($courseid);
                
                if ($userid > 0 && $courseid > 0) {
                    $error_context = "for user '{$user_info['username']}' and course '{$course_info['coursename']}'";
                } else {
                    $error_context = 'during batch operation';
                }
                
                self::log_sync_attempt($userid, $courseid, $request_payload, '', 'error', "$error_message $error_context", '', '');
                curl_close($curl);
                return false;
            }
            
            // Close curl
            curl_close($curl);

            // Parse response and determine status
            $result = json_decode($response);
            $success = isset($result->id) || isset($result->resourceId) || (isset($result->success) && $result->success === true);
            
            // Log the operation result
            $first_completion = reset($completions);
            $userid = $is_vanta_format ? 0 : ($first_completion['userid'] ?? 0);
            $courseid = $is_vanta_format ? 0 : ($first_completion['courseid'] ?? 0);
            $status = $success ? 'success' : 'error';
            $error_message = '';
            
            if (!$success) {
                $error_message = isset($result->error) ? $result->error : 'Unknown error';
                if (isset($result->error_description)) {
                    $error_message .= ': ' . $result->error_description;
                }
            }
            
            $log_message = $error_message ? $error_message . ' (' . $operation_type . ')' : $operation_type;
            
            self::log_sync_attempt(
                $userid, 
                $courseid, 
                $request_payload, 
                $response, 
                $status, 
                $log_message,
                '',
                ''
            );

            return $success;
            
        } catch (Exception $e) {
            // Log exception
            $first_completion = reset($completions);
            $userid = $is_vanta_format ? 0 : ($first_completion['userid'] ?? 0);
            $courseid = $is_vanta_format ? 0 : ($first_completion['courseid'] ?? 0);
            $operation_type = $is_batch ? 'batch operation' : 'single completion';
            
            $user_info = self::get_user_info($userid);
            $course_info = self::get_course_info($courseid);
            
            if ($userid > 0 && $courseid > 0) {
                $exception_context = "for user '{$user_info['username']}' and course '{$course_info['coursename']}'";
            } else {
                $exception_context = 'during ' . $operation_type;
            }
            
            self::log_sync_attempt(
                $userid, 
                $courseid, 
                json_encode($payload ?? []), 
                '', 
                'error', 
                'Exception in ' . $operation_type . ' ' . $exception_context . ': ' . $e->getMessage(),
                '',
                ''
            );
            
            return false;
        }
    }
    
    /**
     * Log a Vanta training sync attempt
     * 
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param string $request_payload API request payload
     * @param string $response_payload API response payload
     * @param string $status Status (success or error)
     * @param string $error_message Error message if any
     * @param string $useremail User email (optional)
     * @param string $coursename Course name (optional)
     * @return int|bool The log ID or false on failure
     */
    public static function log_sync_attempt($userid, $courseid, $request_payload, $response_payload, 
                                           $status, $error_message = '', $useremail = '', $coursename = '') {
        global $DB;
        
        // Handle system/batch operations where userid = 0
        if (empty($useremail)) {
            if ($userid == 0) {
                // For system operations, provide a meaningful label
                if (strpos($error_message, 'Batch operation') !== false) {
                    $useremail = 'Batch Operation';
                } else if (strpos($error_message, 'regeneration') !== false || strpos($error_message, 'JSON file') !== false) {
                    $useremail = 'System (Cron/Regeneration)';
                } else if (strpos($error_message, 'large payload') !== false || strpos($error_message, 'Large payload') !== false) {
                    $useremail = 'System (Large Dataset)';
                } else if (strpos($error_message, 'manual') !== false) {
                    $useremail = 'Manual Operation';
                } else {
                    $useremail = 'All Users';
                }
            } else {
                // Try to get user email from database
                $useremail = $DB->get_field('user', 'email', ['id' => $userid]);
                if (empty($useremail)) {
                    $useremail = "User ID: $userid (not found)";
                }
            }
        }
        
        // Handle system/batch operations where courseid = 0
        if (empty($coursename)) {
            if ($courseid == 0) {
                // Try to extract course names from the request payload for batch operations
                $extracted_course_names = self::extract_course_names_from_payload($request_payload);
                
                if (!empty($extracted_course_names)) {
                    // Show actual course names
                    if (count($extracted_course_names) <= 3) {
                        // Show all course names if 3 or fewer
                        $coursename = implode(', ', $extracted_course_names);
                    } else {
                        // Show first 3 course names + count for more
                        $first_three = array_slice($extracted_course_names, 0, 3);
                        $remaining_count = count($extracted_course_names) - 3;
                        $coursename = implode(', ', $first_three) . " and {$remaining_count} more courses";
                    }
                } else {
                    // Fallback to generic labels for system operations
                    if (strpos($error_message, 'Batch operation') !== false) {
                        $coursename = 'Multiple Courses (Batch)';
                    } else if (strpos($error_message, 'regeneration') !== false || strpos($error_message, 'JSON file') !== false) {
                        $coursename = 'All Configured Courses';
                    } else if (strpos($error_message, 'large payload') !== false || strpos($error_message, 'Large payload') !== false) {
                        $coursename = 'Multiple Courses (Large Dataset)';
                    } else if (strpos($error_message, 'manual') !== false) {
                        $coursename = 'Manual Sync Operation';
                    } else if (strpos($error_message, 'records') !== false) {
                        // Extract record count if available
                        if (preg_match('/(\d+)\s+records?/', $error_message, $matches)) {
                            $coursename = "All Courses ({$matches[1]} records)";
                        } else {
                            $coursename = 'All Courses';
                        }
                    } else {
                        $coursename = 'All Courses';
                    }
                }
            } else {
                // Try to get course name from database
                $coursename = $DB->get_field('course', 'fullname', ['id' => $courseid]);
                if (empty($coursename)) {
                    $coursename = "Course ID: $courseid (not found)";
                }
            }
        }
        
        $log = new \stdClass();
        $log->userid = $userid;
        $log->useremail = $useremail;
        $log->courseid = $courseid;
        $log->coursename = $coursename;
        $log->syncedon = time();
        $log->request_payload = $request_payload;
        $log->response_payload = $response_payload;
        $log->status = $status;
        $log->error_message = $error_message;
        
        try {
            return $DB->insert_record('local_vanta_sync_logs', $log);
        } catch (\Exception $e) {
            if (debugging()) {
                echo '<div class="alert alert-danger">Error logging Vanta sync attempt: ' . $e->getMessage() . '</div>';
            }
            return false;
        }
    }

    /**
     * Extract course names from API request payload for better logging
     * 
     * @param string $request_payload JSON payload sent to API
     * @return array Array of course names found in the payload
     */
    private static function extract_course_names_from_payload($request_payload) {
        global $DB;
        
        if (empty($request_payload)) {
            return [];
        }
        
        try {
            $payload_data = json_decode($request_payload, true);
            if (!$payload_data || !isset($payload_data['resources'])) {
                return [];
            }
            
            $course_names = [];
            foreach ($payload_data['resources'] as $resource) {
                if (isset($resource['trainingName'])) {
                    $course_names[] = $resource['trainingName'];
                }
            }
            
            // Remove duplicates and return unique course names
            return array_unique($course_names);
            
        } catch (\Exception $e) {
            // If JSON parsing fails, return empty array
            return [];
        }
    }

    /**
     * Sync user account information to Vanta.
     *
     * @param int $userid User ID to sync
     * @return bool Success status
     */
    public static function sync_user_account($userid) {
        global $DB, $CFG;

        try {
            // Get access token
            $token = self::get_access_token();
            if (!$token) {
                if (debugging()) {
                    echo '<div class="alert alert-danger">Failed to obtain access token for user sync</div>';
                }
                return false;
            }

            // Get user data
            $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

            // Prepare payload
            $payload = [
                'email' => $user->email,
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'status' => ($user->suspended == 0) ? 'ACTIVE' : 'INACTIVE'
            ];

            // Add optional fields if they exist
            if (!empty($user->phone1)) {
                $payload['phone'] = $user->phone1;
            }

            if (!empty($user->department)) {
                $payload['department'] = $user->department;
            }

            // Convert payload to JSON string
            $request_payload = json_encode($payload);
            
            // Initialize curl
            $curl = curl_init();
            
            // Set curl options with production SSL configuration
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.vanta.com/user/account',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $request_payload,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
                // Production SSL Configuration
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'Moodle-Vanta-Integration/1.0',
            ));
            
            // Execute the request
            $response = curl_exec($curl);
            
            // Check for curl errors
            if (curl_errno($curl)) {
                if (debugging()) {
                    echo '<div class="alert alert-danger">Curl error in user sync: ' . curl_errno($curl) . ' - ' . curl_error($curl) . '</div>';
                }
                curl_close($curl);
                return false;
            }
            
            // Close curl
            curl_close($curl);

            // Parse response
            $result = json_decode($response);
            
            // Log the response for debugging
            if (debugging()) {
                echo '<div class="alert alert-info">Vanta API user sync request: <pre>';
                echo htmlspecialchars($request_payload);
                echo '</pre></div>';
                echo '<div class="alert alert-info">Vanta API user sync response: <pre>';
                echo htmlspecialchars($response);
                echo '</pre></div>';
            }

            return isset($result->id) || (isset($result->success) && $result->success === true);
        } catch (Exception $e) {
            if (debugging()) {
                echo '<div class="alert alert-danger">Exception in sync_user_account: ' . $e->getMessage() . '</div>';
            }
            return false;
        }
    }

    /**
     * Check if a course is configured for Vanta integration.
     *
     * @param int $courseid Course ID to check
     * @return bool Whether the course is configured for integration
     */
    public static function is_course_configured($courseid, $userid, $company_id) {
        global $DB;
        // Get Vanta credential ID for this company
        $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
        if (empty($vanta_id)) {
            return false;   
        }
        
        // Get the rule ID for this Vanta credential
        $rule_id = $DB->get_field('local_vanta_training_sync_rules', 'id', ['vanta_id' => $vanta_id]);
        if (empty($rule_id)) {
            return false;
        }
        
        // Get the sync rule from the database
        $rule = local_vanta_get_training_sync_rules($rule_id);
            
        // Check if we have a valid rule
        if (empty($rule)) {
            return false;
        }
        
        // Check if this course is in the configured courses
        if (empty($rule->courses)) {
            return false;
        }
        
        $course_ids = explode(',', $rule->courses);
        if (in_array($courseid, $course_ids)) {
            // If completion mode is 'any', then just one course is enough
            if ($rule->completion_mode === 'any') {
                return true;
            } else if ($rule->completion_mode === 'all') {
                // If completion mode is 'all', need to check if ALL courses in the sync rule are completed
                $all_courses_in_rule = explode(',', $rule->courses);
                
                // Get completed courses for this user that are in the sync rule
                $completed_course_ids = [];
                foreach ($all_courses_in_rule as $rule_courseid) {
                    $completion = $DB->get_record_sql(
                        "SELECT * FROM {local_coursestatus} 
                         WHERE userid = ? AND courseid = ? 
                         AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                        [$userid, $rule_courseid, 'completed']
                    );
                    
                    if ($completion) {
                        $completed_course_ids[] = $rule_courseid;
                    }
                }
                
                // Check if all required courses in the sync rule are completed
                $all_completed = count($all_courses_in_rule) === count($completed_course_ids);
                if ($all_completed) {
                    return true;
                } else {
                    // Log for debugging
                    $completed_count = count($completed_course_ids);
                    $total_count = count($all_courses_in_rule);
                    self::log_sync_attempt(
                        $userid, 
                        $courseid, 
                        '', 
                        '', 
                        'info', 
                        "All mode: $completed_count of $total_count courses completed in sync rule", 
                        '', 
                        ''
                    );
                }

                return false;
            }
            
            // Default case
            return true;
        }
        
        return false;
    }

    /**
     * Log a course completion and handle sync using the proper queue/JSON file system.
     * This ensures efficient handling with locking, queuing, and incremental updates.
     *
     * @param int $userid User ID who completed the course
     * @param int $courseid Course ID that was completed
     * @return bool Success status
     */
    public static function handle_course_completion_full_sync($userid, $courseid) {
        global $DB, $CFG;
        
        try {
            // Get company ID
            $company_id = $_SESSION['USER']->companyid ?? 0;
            if (!$company_id) {
                // Fallback to get company ID from company_users table if not in session
                $company_id = $DB->get_field_sql(
                    "SELECT cu.companyid 
                     FROM {company_users} cu 
                     WHERE cu.userid = ?", 
                    [$userid]
                );
            }
            
            if (!$company_id) {
                $user_info = self::get_user_info($userid);
                self::log_sync_attempt($userid, $courseid, '', '', 'error', "User '{$user_info['username']}' not associated with any company", '', '');
                return false;
            }
            
            // Get user, course, and company names for better logging
            $username = $DB->get_field('user', 'username', ['id' => $userid]);
            $coursename = $DB->get_field('course', 'fullname', ['id' => $courseid]);
            $companyname = $DB->get_field('company', 'shortname', ['id' => $company_id]);
            
            // Log the completion attempt
            self::log_sync_attempt($userid, $courseid, '', '', 'debug', "Processing completion for user '$username', course '$coursename', company '$companyname'", '', '');
            
            // First, log this completion to our custom table if not already present
            $completion_exists = $DB->record_exists_sql(
                "SELECT 1 FROM {local_coursestatus} 
                 WHERE userid = ? AND courseid = ? 
                 AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                [$userid, $courseid, 'completed']
            );
            
            if (!$completion_exists) {
                // Check if there's an existing record to update
                $existing_record = $DB->get_record('local_coursestatus', [
                    'userid' => $userid,
                    'courseid' => $courseid
                ]);

                if ($existing_record) {
                    // Update existing record
                    $existing_record->course_status = 'completed';
                    $existing_record->completion_date = time();
                    $existing_record->modified_on = time();
                    $DB->update_record('local_coursestatus', $existing_record);
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'debug', "Updated existing completion record for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                } else {
                    // Create new completion record
                    $completion_record = new \stdClass();
                    $completion_record->userid = $userid;
                    $completion_record->courseid = $courseid;
                    $completion_record->course_status = 'completed';
                    $completion_record->completion_date = time();
                    $completion_record->created_on = time();
                    $completion_record->modified_on = time();
                    $DB->insert_record('local_coursestatus', $completion_record);
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'debug', "Created new completion record for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                }
            }
            
            // Check if this course is configured for Vanta integration
            $config_result = self::is_course_configured($courseid, $userid, $company_id);
            if (!$config_result) {
                // Get more specific reason for the configuration failure
                $reason = self::get_course_configuration_reason($courseid, $userid, $company_id);
                self::log_sync_attempt($userid, $courseid, '', '', 'skipped', $reason, '', '');
                return true; // Not an error, just not configured
            }
            
            // Use the proper sync manager workflow
            require_once($CFG->dirroot . '/local/vanta/classes/sync_manager.php');
            
            // Check if sync is currently locked
            if (\local_vanta\sync_manager::is_sync_locked($company_id)) {
                // Sync is locked, queue this completion for later processing
                $queue_result = \local_vanta\sync_manager::queue_completion($userid, $courseid, $company_id, 'sync_locked');
                
                if ($queue_result) {
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'queued', "Completion queued for user '{$user_info['username']}' and course '{$course_info['coursename']}' - sync is locked", '', '');
                    return true; // Successfully queued
                } else {
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "Failed to queue completion for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                    return false;
                }
            }
            
            // Sync is not locked, acquire lock and process immediately
            if (!\local_vanta\sync_manager::acquire_sync_lock($company_id, 'completion_observer')) {
                // Could not acquire lock, queue for later
                $queue_result = \local_vanta\sync_manager::queue_completion($userid, $courseid, $company_id, 'lock_acquisition_failed');
                
                if ($queue_result) {
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'queued', "Completion queued for user '{$user_info['username']}' and course '{$course_info['coursename']}' - could not acquire lock", '', '');
                    return true;
                } else {
                    $user_info = self::get_user_info($userid);
                    $course_info = self::get_course_info($courseid);
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "Failed to queue completion for user '{$user_info['username']}' and course '{$course_info['coursename']}' after lock acquisition failure", '', '');
                    return false;
                }
            }
            
            try {
                // We have the lock, regenerate completion data and send to Vanta
                $user_info = self::get_user_info($userid);
                $course_info = self::get_course_info($courseid);
                self::log_sync_attempt($userid, $courseid, '', '', 'info', "Acquired sync lock for user '{$user_info['username']}' and course '{$course_info['coursename']}', regenerating completion data", '', '');
                
                // Regenerate the complete JSON file with all completion data
                $regenerate_result = \local_vanta\sync_manager::regenerate_completion_data($company_id, false);
                
                if ($regenerate_result) {
                    // Load the generated data
                    $completion_data = \local_vanta\sync_manager::load_completion_data($company_id);
                    
                    if ($completion_data && !empty($completion_data['data'])) {
                        // Send the complete data to Vanta (this replaces all previous data as required by Vanta)
                        $vanta_result = self::send_training_completion($completion_data['data'], null, $company_id);
                        
                        if ($vanta_result) {
                            self::log_sync_attempt($userid, $courseid, '', '', 'success', 
                                "Completion data regenerated and sent to Vanta successfully for user '{$user_info['username']}' and course '{$course_info['coursename']}' (" . count($completion_data['data']) . ' records)', '', '');
                            return true;
                        } else {
                            self::log_sync_attempt($userid, $courseid, '', '', 'error', "Failed to send completion data to Vanta for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                            return false;
                        }
                    } else {
                        self::log_sync_attempt($userid, $courseid, '', '', 'warning', "No completion data found after regeneration for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                        return true; // Not an error, just no data to send
                    }
                } else {
                    self::log_sync_attempt($userid, $courseid, '', '', 'error', "Failed to regenerate completion data for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
                    return false;
                }
                
            } finally {
                // Always release the lock
                \local_vanta\sync_manager::release_sync_lock($company_id);
                $user_info = self::get_user_info($userid);
                $course_info = self::get_course_info($courseid);
                self::log_sync_attempt($userid, $courseid, '', '', 'debug', "Released sync lock for user '{$user_info['username']}' and course '{$course_info['coursename']}'", '', '');
            }
            
        } catch (\Exception $e) {
            // Make sure to release lock on exception
            if (isset($company_id)) {
                \local_vanta\sync_manager::release_sync_lock($company_id);
            }
            
            $user_info = self::get_user_info($userid);
            $course_info = self::get_course_info($courseid);
            self::log_sync_attempt(
                $userid, 
                $courseid, 
                '', 
                '', 
                'error', 
                "Exception in completion sync for user '{$user_info['username']}' and course '{$course_info['coursename']}': " . $e->getMessage(),
                '',
                ''
            );
            return false;
        }
    }

    /**
     * Manually trigger full sync for a specific course.
     * Useful for migrating existing completion data to Vanta.
     *
     * @param int $courseid Course ID to sync
     * @return bool Success status
     */
    public static function manual_full_sync_course($courseid) {
        global $DB;

        try {
            // Get all completed users for this course
            $all_completions = $DB->get_records_sql(
                "SELECT userid, courseid 
                 FROM {local_coursestatus} 
                 WHERE courseid = ? AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                [$courseid, 'completed']
            );

            if (empty($all_completions)) {
                self::log_sync_attempt(0, $courseid, '', '', 'info', 'No completed users found for manual sync', '', '');
                return true; // No users to sync, but not an error
            }

            // Convert to array format expected by send_training_completion
            $completions_array = [];
            foreach ($all_completions as $completion) {
                $completions_array[] = [
                    'userid' => $completion->userid,
                    'courseid' => $completion->courseid
                ];
            }

            // Log that we're performing manual full sync
            self::log_sync_attempt(
                0, 
                $courseid, 
                '', 
                '', 
                'info', 
                'Starting manual full sync for course with ' . count($completions_array) . ' completed users',
                '',
                ''
            );

            // Perform the full sync using the unified function
            $result = self::send_training_completion($completions_array);

            if ($result) {
                self::log_sync_attempt(
                    0, 
                    $courseid, 
                    '', 
                    '', 
                    'success', 
                    'Manual full sync completed successfully for ' . count($completions_array) . ' users',
                    '',
                    ''
                );
            } else {
                self::log_sync_attempt(
                    0, 
                    $courseid, 
                    '', 
                    '', 
                    'error', 
                    'Manual full sync failed for course',
                    '',
                    ''
                );
            }

            return $result;

        } catch (\Exception $e) {
            self::log_sync_attempt(
                0, 
                $courseid, 
                '', 
                '', 
                'error', 
                'Exception in manual full sync: ' . $e->getMessage(),
                '',
                ''
            );
            return false;
        }
    }

    /**
     * Manually trigger batch sync based on completion mode for a sync rule.
     * Useful for administrative batch operations and testing.
     *
     * @param int $rule_id Sync rule ID to process
     * @return bool Success status
     */
    public static function manual_batch_sync_by_rule($rule_id) {
        global $DB;

        try {
            // Get the sync rule
            $sync_rule = local_vanta_get_training_sync_rules($rule_id);
            if (empty($sync_rule)) {
                self::log_sync_attempt(0, 0, '', '', 'error', 'Sync rule not found for manual batch sync', '', '');
                return false;
            }

            // Get all courses in the sync rule
            $all_rule_courses = array_map('trim', explode(',', $sync_rule->courses));
            $all_completions = [];

            // Debug: Log the courses we're working with
            self::log_sync_attempt(
                0, 
                0, 
                '', 
                '', 
                'info', 
                'Processing sync rule with courses: ' . implode(', ', $all_rule_courses) . ' (mode: ' . $sync_rule->completion_mode . ')',
                '',
                ''
            );

            if ($sync_rule->completion_mode === 'any') {
                // ANY mode: Find ALL users who completed ANY of the courses in the sync rule
                // Then send ALL their completed courses from the sync rule
                
                // Create placeholders for IN clause
                $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
                
                // Get all unique users who completed any course from the sync rule
                $sql = "SELECT DISTINCT userid 
                        FROM {local_coursestatus} 
                        WHERE courseid IN ($placeholders) 
                        AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "";
                
                $unique_users = $DB->get_records_sql($sql, array_merge($all_rule_courses, ['completed']));
                
                self::log_sync_attempt(
                    0, 
                    0, 
                    '', 
                    '', 
                    'info', 
                    'ANY mode: Found ' . count($unique_users) . ' users who completed any course',
                    '',
                    ''
                );
                
                // For each user, get all their completed courses from the sync rule
                foreach ($unique_users as $user_record) {
                    $user_completions_sql = "SELECT userid, courseid 
                                           FROM {local_coursestatus} 
                                           WHERE userid = ? 
                                           AND courseid IN ($placeholders) 
                                           AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "";
                    
                    $params = array_merge([$user_record->userid], $all_rule_courses, ['completed']);
                    $user_completed_courses = $DB->get_records_sql($user_completions_sql, $params);
                    
                    foreach ($user_completed_courses as $completion) {
                        $completion_key = $completion->userid . '_' . $completion->courseid;
                        $all_completions[$completion_key] = [
                            'userid' => $completion->userid,
                            'courseid' => $completion->courseid
                        ];
                    }
                }

            } else if ($sync_rule->completion_mode === 'all') {
                // ALL mode: First get ALL completions for the sync rule courses
                $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
                
                // First get all completions for these courses
                $sql = "SELECT cs.userid, cs.courseid, cs.completion_date,
                        u.firstname, u.lastname, u.email,
                        c.fullname as course_name
                        FROM {local_coursestatus} cs
                        JOIN {user} u ON u.id = cs.userid
                        JOIN {course} c ON c.id = cs.courseid
                        WHERE cs.courseid IN ($placeholders) 
                        AND " . $DB->sql_compare_text('cs.course_status') . " = " . $DB->sql_compare_text('?') . "
                        ORDER BY cs.userid, cs.courseid";
                
                $all_course_completions = $DB->get_records_sql($sql, array_merge($all_rule_courses, ['completed']));
                
                // Group completions by user to check who completed all courses
                $user_completions = [];
                foreach ($all_course_completions as $completion) {
                    if (!isset($user_completions[$completion->userid])) {
                        $user_completions[$completion->userid] = [
                            'courses' => [],
                            'user_info' => [
                                'firstname' => $completion->firstname,
                                'lastname' => $completion->lastname,
                                'email' => $completion->email
                            ]
                        ];
                    }
                    $user_completions[$completion->userid]['courses'][$completion->courseid] = [
                        'completion_date' => $completion->completion_date,
                        'course_name' => $completion->course_name
                    ];
                }

                // Log the initial findings
                self::log_sync_attempt(
                    0,
                    0,
                    json_encode($user_completions),
                    '',
                    'debug',
                    sprintf('Found completions for %d users across sync rule courses', count($user_completions)),
                    '',
                    ''
                );

                // Now check which users completed all required courses
                $required_course_count = count($all_rule_courses);
                $users_with_all_completions = [];
                
                foreach ($user_completions as $user_id => $data) {
                    $completed_course_count = count($data['courses']);
                    if ($completed_course_count >= $required_course_count) {
                        // This user has completed all required courses
                        foreach ($data['courses'] as $course_id => $course_data) {
                            $completion_key = $user_id . '_' . $course_id;
                            $all_completions[$completion_key] = [
                                'userid' => $user_id,
                                'courseid' => $course_id,
                                'completion_date' => $course_data['completion_date']
                            ];
                        }
                        $users_with_all_completions[] = $user_id;
                    }
                }

                // Log the results
                self::log_sync_attempt(
                    0,
                    0,
                    '',
                    '',
                    'info',
                    sprintf('Found %d users who completed all %d required courses', 
                           count($users_with_all_completions), 
                           $required_course_count),
                    '',
                    ''
                );

            } else {
                self::log_sync_attempt(0, 0, '', '', 'error', 'Unsupported completion mode for manual batch sync', '', '');
                return false;
            }

            // Convert associative array back to indexed array
            $all_completions = array_values($all_completions);

            if (empty($all_completions)) {
                self::log_sync_attempt(0, 0, '', '', 'info', 'No qualifying completions found for manual batch sync', '', '');
                return true; // Not an error, just no qualifying completions
            }

            // Log what we're about to sync
            $sync_mode = $sync_rule->completion_mode ?? 'default';
            $completion_count = count($all_completions);
            $unique_users = count(array_unique(array_column($all_completions, 'userid')));
            $unique_courses = count(array_unique(array_column($all_completions, 'courseid')));
            
            self::log_sync_attempt(
                0, 
                0, 
                '', 
                '', 
                'info', 
                "Starting manual batch sync ({$sync_mode} mode): {$completion_count} completion(s) for {$unique_users} user(s) across {$unique_courses} course(s)",
                '',
                ''
            );

            // Perform the sync using the unified function
            $result = self::send_training_completion($all_completions);

            if ($result) {
                self::log_sync_attempt(
                    0, 
                    0, 
                    '', 
                    '', 
                    'success', 
                    "Manual batch sync completed successfully ({$sync_mode} mode): {$completion_count} completion(s) for {$unique_users} user(s)",
                    '',
                    ''
                );
            } else {
                self::log_sync_attempt(
                    0, 
                    0, 
                    '', 
                    '', 
                    'error', 
                    "Manual batch sync failed ({$sync_mode} mode): {$completion_count} completion(s)",
                    '',
                    ''
                );
            }

            return $result;

        } catch (\Exception $e) {
            self::log_sync_attempt(
                0, 
                0, 
                '', 
                '', 
                'error', 
                'Exception in manual batch sync: ' . $e->getMessage(),
                '',
                ''
            );
            return false;
        }
    }

    /**
     * Debug function to help troubleshoot completion data issues.
     *
     * @param string $message The debug message to log
     * @return void
     */
    public static function debug($message) {
        if (debugging()) {
            echo '<div class="alert alert-info">' . $message . '</div>';
        }
    }

    /**
     * Debug function to check completion data for a sync rule.
     * Useful for troubleshooting missing data issues.
     *
     * @param int $rule_id Sync rule ID to check
     * @return array Debug information
     */
    public static function debug_completion_data($rule_id) {
        global $DB;
        
        $debug_info = [];
        
        try {
            // Get the sync rule
            $sync_rule = local_vanta_get_training_sync_rules($rule_id);
            if (empty($sync_rule)) {
                return ['error' => 'Sync rule not found'];
            }
            
            $all_rule_courses = array_map('trim', explode(',', $sync_rule->courses));
            $debug_info['sync_rule'] = $sync_rule;
            $debug_info['courses'] = $all_rule_courses;
            
            // Check total completions in local_coursestatus
            $total_completions = $DB->count_records_sql(
                "SELECT COUNT(*) FROM {local_coursestatus} 
                 WHERE " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                ['completed']
            );
            $debug_info['total_completions_in_system'] = $total_completions;
            
            // Check completions for each course in the sync rule
            $course_completion_data = [];
            foreach ($all_rule_courses as $course_id) {
                $completions = $DB->get_records_sql(
                    "SELECT id, userid, courseid, course_status, completion_date 
                     FROM {local_coursestatus} 
                     WHERE courseid = ? AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                    [$course_id, 'completed']
                );
                
                $course_completion_data[$course_id] = [
                    'count' => count($completions),
                    'completions' => $completions
                ];
            }
            $debug_info['course_completions'] = $course_completion_data;
            
            // Check users who completed ANY course
            if (!empty($all_rule_courses)) {
                $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
                $any_users = $DB->get_records_sql(
                    "SELECT DISTINCT userid 
                     FROM {local_coursestatus} 
                     WHERE courseid IN ($placeholders) 
                     AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                    array_merge($all_rule_courses, ['completed'])
                );
                $debug_info['users_with_any_completion'] = count($any_users);
                $debug_info['any_users_list'] = array_keys($any_users);
            }
            
            // Check users who completed ALL courses
            if (count($all_rule_courses) > 1) {
                $course_count = count($all_rule_courses);
                $placeholders = str_repeat('?,', count($all_rule_courses) - 1) . '?';
                
                $all_users = $DB->get_records_sql(
                    "SELECT userid, COUNT(DISTINCT courseid) as completed_count
                     FROM {local_coursestatus} 
                     WHERE courseid IN ($placeholders) 
                     AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "
                     GROUP BY userid
                     HAVING COUNT(DISTINCT courseid) = ?",
                    array_merge($all_rule_courses, ['completed', $course_count])
                );
                $debug_info['users_with_all_completions'] = count($all_users);
                $debug_info['all_users_list'] = array_keys($all_users);
            }
            
            return $debug_info;
            
        } catch (\Exception $e) {
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Send large datasets to Vanta API with chunking for performance
     * 
     * @param string $token API token
     * @param string $resource_id Vanta resource ID  
     * @param array $completions Array of completion data
     * @param int $chunk_size Number of records per chunk
     * @return bool Success status
     */
    private static function send_large_dataset_chunked($token, $resource_id, $completions, $chunk_size = 500) {
        global $DB;
        
        $total_records = count($completions);
        $total_chunks = ceil($total_records / $chunk_size);
        
        self::log_sync_attempt(0, 0, '', '', 'info', 
            "Starting chunked processing: $total_records records in $total_chunks chunks of $chunk_size each", '', '');
        
        $successful_chunks = 0;
        $failed_chunks = 0;
        $total_start_time = microtime(true);
        
        for ($i = 0; $i < $total_chunks; $i++) {
            $chunk_start_time = microtime(true);
            $offset = $i * $chunk_size;
            $chunk = array_slice($completions, $offset, $chunk_size);
            $chunk_number = $i + 1;
            
            self::log_sync_attempt(0, 0, '', '', 'info', 
                "Processing chunk $chunk_number/$total_chunks (" . count($chunk) . " records)", '', '');
            
            // Prepare chunk payload
            $payload = [
                "resourceId" => $resource_id,
                "resources" => $chunk
            ];
            
            $request_payload = json_encode($payload);
            
            // Log memory usage for monitoring
            $memory_mb = round(memory_get_usage(true) / 1024 / 1024, 2);
            self::log_sync_attempt(0, 0, '', '', 'info', 
                "Chunk $chunk_number memory usage: {$memory_mb}MB", '', '');
            
            // Send chunk to API
            $result = self::send_to_vanta_api($token, $request_payload);
            
            $chunk_time = round(microtime(true) - $chunk_start_time, 2);
            
            if ($result) {
                $successful_chunks++;
                self::log_sync_attempt(0, 0, '', '', 'success', 
                    "Chunk $chunk_number sent successfully in {$chunk_time}s", '', '');
            } else {
                $failed_chunks++;
                self::log_sync_attempt(0, 0, '', '', 'error', 
                    "Chunk $chunk_number failed after {$chunk_time}s", '', '');
            }
            
            // Rate limiting: brief pause between chunks
            if ($i < $total_chunks - 1) {
                usleep(100000); // 100ms delay
            }
            
            // Force garbage collection for memory management
            if ($i % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        $total_time = round(microtime(true) - $total_start_time, 2);
        $final_memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        
        self::log_sync_attempt(0, 0, '', '', 'info', 
            "Chunked processing complete: $successful_chunks successful, $failed_chunks failed. Total time: {$total_time}s, Final memory: {$final_memory}MB", '', '');
        
        return $failed_chunks === 0;
    }

    /**
     * Send data to Vanta API with optimizations for large payloads
     * 
     * @param string $token API token
     * @param string $payload JSON payload to send
     * @return bool Success status  
     */
    private static function send_to_vanta_api($token, $payload) {
        $vanta_api_url = 'https://api.vanta.com/v1/external/training';
        
        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];
        
        // Initialize cURL with production SSL configuration
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $vanta_api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120, // Extended timeout for large payloads
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'Moodle-Vanta-Integration/1.0',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // Production SSL Configuration
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            // Memory optimizations
            CURLOPT_BUFFERSIZE => 131072, // 128KB buffer
            CURLOPT_TCP_NODELAY => true,
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log API call details
        $payload_size = round(strlen($payload) / 1024, 2); // Size in KB
        
        if ($response === false || !empty($error)) {
            self::log_sync_attempt(0, 0, '', '', 'error', 
                "cURL error: $error. Payload size: {$payload_size}KB", '', '');
            return false;
        }
        
        if ($http_code >= 200 && $http_code < 300) {
            self::log_sync_attempt(0, 0, '', '', 'success', 
                "API call successful (HTTP $http_code). Payload size: {$payload_size}KB", '', '');
            return true;
        } else {
            self::log_sync_attempt(0, 0, '', '', 'error', 
                "API call failed (HTTP $http_code). Response: $response. Payload size: {$payload_size}KB", '', '');
            return false;
        }
    }

    /**
     * Optimize for large single payload to Vanta API
     * 
     * @param string $token API token
     * @param string $resource_id Vanta resource ID
     * @param array $completions Array of completion data  
     * @return bool Success status
     */
    private static function send_large_payload_optimized($token, $resource_id, $completions) {
        $total_records = count($completions);
        $start_time = microtime(true);
        $memory_start = memory_get_usage(true);
        
        self::log_sync_attempt(0, 0, '', '', 'info', 
            "Preparing large payload: $total_records records. Memory: " . round($memory_start / 1024 / 1024, 2) . "MB", '', '');
        
        // Check if we need to increase memory/time limits for very large datasets
        if ($total_records > 15000) {
            $current_memory = ini_get('memory_limit');
            $current_time = ini_get('max_execution_time');
            
            // Increase limits if needed
            if (self::parse_memory_limit($current_memory) < 512 * 1024 * 1024) {
                ini_set('memory_limit', '512M');
                self::log_sync_attempt(0, 0, '', '', 'warning', 
                    "Increased memory limit to 512M for large dataset", '', '');
            }
            
            if ($current_time < 300) {
                ini_set('max_execution_time', 300);
                self::log_sync_attempt(0, 0, '', '', 'warning', 
                    "Increased execution time to 300s for large dataset", '', '');
            }
        }
        
        // Prepare the complete payload (Vanta expects ALL data at once)
        $payload = [
            "resourceId" => $resource_id,
            "resources" => $completions
        ];
        
        // Convert to JSON with memory optimization
        $request_payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        $memory_after_json = memory_get_usage(true);
        $payload_size_mb = round(strlen($request_payload) / 1024 / 1024, 2);
        $memory_usage_mb = round($memory_after_json / 1024 / 1024, 2);
        
        self::log_sync_attempt(0, 0, '', '', 'info', 
            "Payload created: {$payload_size_mb}MB JSON, Memory usage: {$memory_usage_mb}MB", '', '');
        
        // Send to Vanta API
        $result = self::send_to_vanta_api($token, $request_payload);
        
        $total_time = round(microtime(true) - $start_time, 2);
        $final_memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        
        if ($result) {
            self::log_sync_attempt(0, 0, '', '', 'success', 
                "Large payload sent successfully: $total_records records in {$total_time}s. Final memory: {$final_memory}MB", '', '');
        } else {
            self::log_sync_attempt(0, 0, '', '', 'error', 
                "Large payload failed: $total_records records after {$total_time}s. Final memory: {$final_memory}MB", '', '');
        }
        
        // Cleanup
        unset($payload, $request_payload, $completions);
        gc_collect_cycles();
        
        return $result;
    }
    
    /**
     * Parse memory limit string to bytes
     * 
     * @param string $limit Memory limit string (e.g., "256M", "1G")
     * @return int Memory limit in bytes
     */
    private static function parse_memory_limit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $number = intval($limit);
        
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
     * Get a specific reason why a course is not configured for Vanta sync.
     * This provides more detailed feedback than just "Course not configured".
     *
     * @param int $courseid Course ID to check
     * @param int $userid User ID to check
     * @param int $company_id Company ID to check
     * @return string Specific reason message
     */
    public static function get_course_configuration_reason($courseid, $userid, $company_id) {
        global $DB;
        
        // Get Vanta credential ID for this company
        $vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);
        if (empty($vanta_id)) {
            return 'No Vanta API credentials configured for this company';   
        }
        
        // Get the rule ID for this Vanta credential
        $rule_id = $DB->get_field('local_vanta_training_sync_rules', 'id', ['vanta_id' => $vanta_id]);
        if (empty($rule_id)) {
            return 'No training sync rules configured for Vanta integration';
        }
        
        // Get the sync rule from the database
        $rule = local_vanta_get_training_sync_rules($rule_id);
            
        // Check if we have a valid rule
        if (empty($rule)) {
            return 'Training sync rule not found or invalid';
        }
        
        // Check if this course is in the configured courses
        if (empty($rule->courses)) {
            return 'No courses configured in the training sync rule';
        }
        
        $course_ids = explode(',', $rule->courses);
        if (!in_array($courseid, $course_ids)) {
            return 'Course not included in the training sync rule';
        }
        
        // If we're here, the course is configured but might fail "all" mode
        if ($rule->completion_mode === 'all') {
            // Check if all required courses are completed
            $all_courses_in_rule = explode(',', $rule->courses);
            
            // Get completed courses for this user that are in the sync rule
            $completed_course_ids = [];
            foreach ($all_courses_in_rule as $rule_courseid) {
                $completion = $DB->get_record_sql(
                    "SELECT * FROM {local_coursestatus} 
                     WHERE userid = ? AND courseid = ? 
                     AND " . $DB->sql_compare_text('course_status') . " = " . $DB->sql_compare_text('?') . "",
                    [$userid, $rule_courseid, 'completed']
                );
                
                if ($completion) {
                    $completed_course_ids[] = $rule_courseid;
                }
            }
            
            $completed_count = count($completed_course_ids);
            $total_count = count($all_courses_in_rule);
            
            if ($completed_count < $total_count) {
                return "User has completed $completed_count of $total_count required courses. All courses must be completed as per the training sync rule.";
            }
        }
        
        // Default case - shouldn't reach here normally
        return 'Course not configured for Vanta sync';
    }
}

/**
 * Get Vanta API credentials from the database.
 *
 * @param int $company_id Company ID, or 0 for system-wide credentials
 * @return object|false The credentials record or false if not found
 */
function local_vanta_get_api_credentials($company_id = 0) {
    global $DB;
    
    $params = [
        'company_id' => $company_id,
        'deleted' => 0,
        'status' => 1
    ];
    
    return $DB->get_record('local_vanta_api_credentials', $params);
}

/**
 * Save new Vanta API credentials.
 *
 * @param object $data The credentials data
 * @param int $user_id The user ID performing the action, or 0 for system
 * @return int|bool The ID of the new record, or false on failure
 */
function local_vanta_save_api_credentials($data, $user_id = 0) {
    global $DB;
    
    try {
        $record = new \stdClass();
        $record->name = $data->name;
        $record->client_id = $data->clientid;
        $record->client_secret = $data->clientsecret;
        $record->resource_id = "resourceid";
        $record->scope = $data->scope ?? 'connectors.self:write-resource';
        $record->grant_type = $data->granttype ?? 'client_credentials';
        $record->company_id = $data->companyid ?? 0;
        $record->status = $data->status ?? 1;
        $record->deleted = 0;
        $record->created_by = $user_id;
        $record->created_on = time();
        $id = $DB->insert_record('local_vanta_api_credentials', $record);
        if ($id) {
            $id = jwtsecure::Encode($id);
            return $id;
        }
        return false;
    } catch (\Exception $e) {
      return false;
    }
}

/**
 * Update existing Vanta API credentials.
 *
 * @param object $data The credentials data with id
 * @param int $user_id The user ID performing the action, or 0 for system
 * @return bool Success status
 */
function local_vanta_update_api_credentials($data, $user_id = 0) {
    global $DB;
    
    // Check if record exists
    if (!$DB->record_exists('local_vanta_api_credentials', ['id' => $data->id])) {
        return false;
    }
    
    // Prepare the record
    $record = new \stdClass();
    $record->id = $data->id;
    $record->name = $data->name;
    // Map field names correctly
    $record->client_id = $data->clientid;
    $record->client_secret = $data->clientsecret;
    
    if (isset($data->resourceid)) {
        $record->resource_id = $data->resourceid;
    }
    
    if (isset($data->scope)) {
        $record->scope = $data->scope;
    }
    
    if (isset($data->grant_type)) {
        $record->grant_type = $data->grant_type;
    }
    
    if (isset($data->companyid)) {
        $record->company_id = $data->companyid;
    } else if (isset($data->company_id)) {
        $record->company_id = $data->company_id;
    }
    
    if (isset($data->status)) {
        $record->status = $data->status;
    }
    
    $record->modified_by = $user_id;
    $record->modified_on = time();
    
    $result = $DB->update_record('local_vanta_api_credentials', $record);
    
    return $result;
}

/**
 * Delete Vanta API credentials.
 *
 * @param int $id Record ID
 * @param int $user_id User ID performing the deletion
 * @return bool Success status
 */
function local_vanta_delete_api_credentials($id, $user_id = 0) {
    global $DB, $USER;

    if (empty($user_id)) {
        $user_id = $USER->id;
    }

    try {
        $record = $DB->get_record('local_vanta_api_credentials', ['id' => $id]);
        if (!$record) {
            return false;
        }

        // Mark as deleted instead of actual deletion
        $record->deleted = 1;
        $record->modified_by = $user_id;
        $record->modified_on = time();
        
        return $DB->update_record('local_vanta_api_credentials', $record);
    } catch (Exception $e) {
        if (debugging()) {
            echo '<div class="alert alert-danger">Error deleting Vanta API credentials: ' . $e->getMessage() . '</div>';
        }
        return false;
    }
}

/**
 * Get training sync rules by ID or get all rules if no ID is provided.
 *
 * @param int|null $id Optional rule ID to retrieve specific record
 * @return object|array|false The rule record, array of records, or false if not found
 */
function local_vanta_get_training_sync_rules($id = null) {
    global $DB;
    try {
        if ($id !== null) {
            return $DB->get_record('local_vanta_training_sync_rules', ['id' => (int) $id]);
        } else {
            return $DB->get_records('local_vanta_training_sync_rules');
        }   
    } catch (Exception $e) {
        if (debugging()) {
            echo '<div class="alert alert-danger">Error retrieving Vanta training sync rules: ' . $e->getMessage() . '</div>';
        }
        return false;
    }
}

/**
 * Insert a new training sync rule.
 *
 * @param object $data The data to insert
 * @param int $user_id User ID performing the insertion
 * @return int|bool The ID of the inserted record or false on failure
 */
function local_vanta_save_training_sync_rule($data, $user_id = 0) {
    global $DB, $USER;
    
    if (empty($user_id)) {
        $user_id = $USER->id;
    }
    
    try {
        $record = new stdClass();
        $record->vanta_id = $data->vanta_id;
        $record->resource_id = $data->resource_id;
        
        // Handle array to string conversion for frameworks and courses
        if (isset($data->frameworks)) {
            if (is_array($data->frameworks)) {
                $record->frameworks = implode(',', $data->frameworks);
            } else {
                $record->frameworks = $data->frameworks;
            }
        }
        
        if (isset($data->courses)) {
            if (is_array($data->courses)) {
                $record->courses = implode(',', $data->courses);
            } else {
                $record->courses = $data->courses;
            }
        }
        
        $record->completion_mode = isset($data->completion_mode) ? $data->completion_mode : 'any';
        $record->created_by = $user_id;
        $record->created_on = time();
        $record->modified_by = $user_id;
        $record->modified_on = time();
        
        $id = $DB->insert_record('local_vanta_training_sync_rules', $record);
        
        if ($id) {
            return $id;
        }
        
        return false;
    } catch (Exception $e) {
        if (debugging()) {
            echo '<div class="alert alert-danger">Error saving Vanta training sync rule: ' . $e->getMessage() . '</div>';
        }
        return false;
    }
}

/**
 * Update an existing training sync rule.
 *
 * @param object $data The data to update
 * @param int $user_id User ID performing the update
 * @return bool Success status
 */
function local_vanta_update_training_sync_rule($data, $user_id = 0) {
    global $DB, $USER;
    
    if (empty($user_id)) {
        $user_id = $USER->id;
    }
    
    if (empty($data->id)) {
        return false;
    }
    
    try {
        $record = $DB->get_record('local_vanta_training_sync_rules', ['id' => $data->id]);
        
        if (!$record) {
            return false;
        }
        
        if (isset($data->vanta_id)) {
            $record->vanta_id = $data->vanta_id;
        }
        
        if (isset($data->resource_id)) {
            $record->resource_id = $data->resource_id;
        }
        
        // Handle array to string conversion for frameworks and courses
        if (isset($data->frameworks)) {
            if (is_array($data->frameworks)) {
                $record->frameworks = implode(',', $data->frameworks);
            } else {
                $record->frameworks = $data->frameworks;
            }
        }
        
        if (isset($data->courses)) {
            if (is_array($data->courses)) {
                $record->courses = implode(',', $data->courses);
            } else {
                $record->courses = $data->courses;
            }
        }
        
        if (isset($data->completion_mode)) {
            $record->completion_mode = $data->completion_mode;
        }
        
        $record->modified_by = $user_id;
        $record->modified_on = time();
        
        return $DB->update_record('local_vanta_training_sync_rules', $record);
    } catch (Exception $e) {
        if (debugging()) {
            echo '<div class="alert alert-danger">Error updating Vanta training sync rule: ' . $e->getMessage() . '</div>';
        }
        return false;
    }
}

/**
 * Delete a training sync rule.
 *
 * @param int $id Record ID
 * @return bool Success status
 */
function local_vanta_delete_training_sync_rule($id) {
    global $DB;
    
    try {
        return $DB->delete_records('local_vanta_training_sync_rules', ['id' => $id]);
    } catch (Exception $e) {
        if (debugging()) {
            echo '<div class="alert alert-danger">Error deleting Vanta training sync rule: ' . $e->getMessage() . '</div>';
        }
        return false;
    }
}

/**
 * Create navigation tabs for Vanta plugin pages.
 *
 * @param array|object $params Parameters including vanta_id (optional)
 * @param string $page Current page identifier (settings, training_sync_rules, logs)
 * @return tabtree The tab tree object for rendering
 */
function local_vanta_tabs($params, $page = 'settings') {
    
    $params = (object)$params;
    
    $tabs = array();
    
    switch ($page) {
        case 'settings':
            $currenttab = 'settings';
            break;
        case 'training_sync_rules':
            $currenttab = 'training_sync_rules';
            break;
        case 'logs':
            $currenttab = 'logs';
            break;
        default:
            $currenttab = 'settings';
            break;
    }
    
    // Get vanta_id if available, otherwise use empty for basic access
    $vanta_id = isset($params->vanta_id) ? $params->vanta_id : '';
    
    // Settings tab (always accessible)
    $settings_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
    $tabs[] = new tabobject(
        'settings',
        new moodle_url('/local/vanta/index.php', $settings_params),
        get_string('settings', 'local_vanta')
    );
    
    // Training Sync Rules tab (always accessible)
    $rules_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
    $tabs[] = new tabobject(
        'training_sync_rules',
        new moodle_url('/local/vanta/training_sync_rules.php', $rules_params),
        get_string('training_sync_rules', 'local_vanta')
    );
    
    // Logs tab (always accessible)
    $logs_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
    $tabs[] = new tabobject(
        'logs',
        new moodle_url('/local/vanta/logs.php', $logs_params),
        get_string('logs', 'local_vanta')
    );
    
    return new tabtree($tabs, $currenttab);
}