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
 * Vanta Integration Logs
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use core\context\system as context_system;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->dirroot . '/composer/jwtsecure.php');
defined('MOODLE_INTERNAL') || die();
// Require login and verify capabilities
require_login();
$context = context_system::instance();
// require_capability('moodle/site:config', $context);

// Get vanta_id parameter (optional)
$vanta_id = optional_param('vanta_id', '', PARAM_RAW);

// Set up the page
$PAGE->set_context($context);
$url_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
$PAGE->set_url(new moodle_url('/local/vanta/logs.php', $url_params));
$PAGE->set_pagelayout('administrator');
$PAGE->set_title(get_string('pluginname', 'local_vanta') . ': ' . get_string('logs', 'local_vanta'));
$PAGE->set_heading(get_string('pluginname', 'local_vanta') . ': ' . get_string('logs', 'local_vanta'));

// Create navigation tabs using the helper function
$tab_params = ['vanta_id' => $vanta_id];
$tabs = local_vanta_tabs($tab_params, 'logs');

// Set up filtering options
$useremail = optional_param('useremail', '', PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$status = optional_param('status', '', PARAM_TEXT);
$fromdate_str = optional_param('fromdate', '', PARAM_TEXT);
$todate_str = optional_param('todate', '', PARAM_TEXT);

// Convert date strings to timestamps
$fromdate = 0;
$todate = 0;
if (!empty($fromdate_str)) {
    $fromdate = strtotime($fromdate_str . ' 00:00:00');
}
if (!empty($todate_str)) {
    $todate = strtotime($todate_str . ' 23:59:59');
}

// Validate that to date is not less than from date
$date_validation_error = '';
$fromdate_error = '';
$todate_error = '';
if (!empty($fromdate_str) && !empty($todate_str) && $todate < $fromdate) {
    $date_validation_error = get_string('date_validation_error', 'local_vanta');
    $todate_error = get_string('date_validation_error', 'local_vanta');
    // Reset the dates to prevent filtering with invalid date range
    $fromdate = 0;
    $todate = 0;
    $fromdate_str = '';
    $todate_str = '';
}

// Setup pagination
$page = optional_param('page', 0, PARAM_INT);
$perpage = 10; // Set to 10 records per page

// Build WHERE clause and parameters for filtering
$where_conditions = [];
$sql_params = [];

if (!empty($useremail)) {
    $where_conditions[] = "useremail LIKE :useremail";
    $sql_params['useremail'] = '%' . $useremail . '%';
}

if (!empty($courseid)) {
    $where_conditions[] = "courseid = :courseid";
    $sql_params['courseid'] = $courseid;
}

if (!empty($status) && in_array($status, ['success', 'error', 'info', 'skipped'])) {
    $where_conditions[] = "status = :status";
    $sql_params['status'] = $status;
}

if (!empty($fromdate)) {
    $where_conditions[] = "syncedon >= :fromdate";
    $sql_params['fromdate'] = $fromdate;
}

if (!empty($todate)) {
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

// Get all courses for filter dropdown
$all_courses = $DB->get_records_sql(
    'SELECT DISTINCT l.courseid, l.coursename 
     FROM {local_vanta_sync_logs} l
     INNER JOIN {course} c ON c.id = l.courseid
     WHERE l.courseid > 0 
       AND l.coursename IS NOT NULL 
       AND l.coursename != ""
       AND c.id != 1
     ORDER BY l.coursename'
);

$course_options = [];
foreach ($all_courses as $course) {
    $course_options[] = [
        'id' => $course->courseid,
        'name' => $course->coursename,
        'selected' => ($courseid == $course->courseid)
    ];
}

// Format the log data for the template
$formatted_logs = [];
foreach ($logs as $log) {
    // Ensure status has a fallback value
    $log_status = !empty($log->status) ? $log->status : 'unknown';
    
    $formatted_logs[] = [
        'id' => $log->id,
        'useremail' => $log->useremail,
        'coursename' => $log->coursename,
        'syncedon' => $log->syncedon,
        'syncedon_formatted' => userdate($log->syncedon, get_string('strftimedatetime', 'langconfig')),
        'status' => $log_status,
        'status_class' => $log_status === 'success' ? 'text-success' : ($log_status === 'error' ? 'text-danger' : 'text-info'),
        'status_success' => $log_status === 'success',
        'status_error' => $log_status === 'error',
        'status_info' => $log_status === 'info',
        'status_skipped' => $log_status === 'skipped',
        'status_unknown' => $log_status === 'unknown',
        'has_error' => !empty($log->error_message),
        'error_message' => $log->error_message,
        'view_url' => new moodle_url('/local/vanta/view_log.php', ['id' => jwtsecure::encode(['id' => $log->id])])
    ];
}

// Setup pagination URLs
$baseurl = new moodle_url('/local/vanta/logs.php', [
    'vanta_id' => $vanta_id,
    'useremail' => $useremail,
    'courseid' => $courseid,
    'status' => $status,
    'fromdate' => $fromdate_str,
    'todate' => $todate_str
]);

// Create pagination
$pagingbar = new paging_bar($total_logs, $page, $perpage, $baseurl);

// Prepare template data
$data = [
    'vanta_id' => $vanta_id,
    'filter_form' => [
        'action' => new moodle_url('/local/vanta/logs.php', ['vanta_id' => $vanta_id]),
        'method' => 'post',
        'id' => 'vanta-logs-filter-form'
    ],
    'has_logs' => !empty($formatted_logs),
    'total_logs' => $total_logs,
    'showing_from' => $offset + 1,
    'showing_to' => min($offset + $perpage, $total_logs),
    'pagingbar' => $OUTPUT->render($pagingbar),
    'has_pagination' => $total_logs > $perpage,
    'useremail' => $useremail,
    'courseid' => $courseid,
    'status_success' => (!empty($status) && $status === 'success'),
    'status_error' => (!empty($status) && $status === 'error'),
    'status_info' => (!empty($status) && $status === 'info'),
    'status_skipped' => (!empty($status) && $status === 'skipped'),
    'status_all' => empty($status), // This will be true when no status is selected (default)
    'fromdate' => $fromdate_str,
    'todate' => $todate_str,
    'baseurl' => $baseurl->out_omit_querystring(),
    'reset_url' => (new moodle_url('/local/vanta/logs.php', ['vanta_id' => $vanta_id]))->out(),
    'courses' => $course_options,
    'logs' => $formatted_logs,
    'date_validation_error' => $date_validation_error,
    'has_date_validation_error' => !empty($date_validation_error),
    'fromdate_error' => $fromdate_error,
    'todate_error' => $todate_error
];

// Add required JavaScript modules
$PAGE->requires->js_call_amd('local_vanta/logs', 'init');

// Add inline styles to prevent DataTables interference
$inline_css = '
<style>
#vanta-logs-display-table.dataTable {
    width: 100% !important;
}
#vanta-logs-display-table thead th {
    background-image: none !important;
    cursor: default !important;
    padding-right: 8px !important;
}
#vanta-logs-display-table thead th:after {
    display: none !important;
}
.dataTables_wrapper {
    display: none !important;
}
#vanta-logs-display-table_wrapper .dataTables_paginate,
#vanta-logs-display-table_wrapper .dataTables_info,
#vanta-logs-display-table_wrapper .dataTables_length,
#vanta-logs-display-table_wrapper .dataTables_filter {
    display: none !important;
}
</style>
';

// Display the page
echo $OUTPUT->header();
echo $inline_css;
echo $OUTPUT->render($tabs);
echo $OUTPUT->render_from_template('local_vanta/logs', $data);
echo $OUTPUT->footer(); 