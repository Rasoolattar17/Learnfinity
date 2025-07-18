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
 * Vanta integration completion sync rules configuration page
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tabobject;
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->dirroot . '/lib/weblib.php'); // For tabobject class
require_once($CFG->libdir . '/xmldb/xmldb_table.php');

use core\output\notification;
use core\context\system as context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

// Security checks.
require_login();
$context = context_system::instance();
// require_capability('moodle/site:config', $context);

$PAGE->set_pagelayout('administrator');
$vanta_id = optional_param('vanta_id', '', PARAM_RAW);

// Page setup.
$url_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
$url = new moodle_url('/local/vanta/training_sync_rules.php', $url_params);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('training_sync_rules', 'local_vanta'));
$PAGE->set_heading(get_string('training_sync_rules', 'local_vanta'));

// Create navigation tabs using the helper function
$tab_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
$tabs = local_vanta_tabs($tab_params, 'training_sync_rules');

$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/vanta/style/training_sync_rules.css'));

// Fetch data from the database instead of config
$selected_frameworks = [];
$selected_courses = [];
$completion_mode = 'any';
$resource_id = '';

// Get company ID safely
$company_id = 0;
if (isset($_SESSION['USER']) && isset($_SESSION['USER']->companyid)) {
    $company_id = $_SESSION['USER']->companyid;
}

$vanta_id = $DB->get_field('local_vanta_api_credentials', 'id', ['company_id' => $company_id]);

// Get the default sync rule (using vanta_id = 'default')
$sync_rule = $vanta_id ?  $DB->get_record('local_vanta_training_sync_rules', ['vanta_id' => $vanta_id]) : null ;

if ($sync_rule) {
    // Parse frameworks and courses from the database record
    if (!empty($sync_rule->frameworks)) {
        $selected_frameworks = explode(',', $sync_rule->frameworks);
    }
    
    if (!empty($sync_rule->courses)) {
        $selected_courses = explode(',', $sync_rule->courses);
    }
    
    $completion_mode = $sync_rule->completion_mode;
    $resource_id = $sync_rule->resource_id;
}

// Framework options are fixed
$frameworks_data = [];
$framework_options = [
    'SOC2' => 'SOC2',
    'ISO27001' => 'ISO 27001',
    'HIPAA' => 'HIPAA',
    'PCI' => 'PCI',
    'GDPR' => 'GDPR',
    'CCPA' => 'CCPA'
];

// Prepare selected frameworks data
$selected_frameworks_data = [];
foreach ($framework_options as $id => $name) {
    $selected_frameworks_data[$id] = in_array($id, $selected_frameworks);
}

// Get company ID safely
$company_id = 0;
if (isset($_SESSION['USER']) && isset($_SESSION['USER']->companyid)) {
    $company_id = $_SESSION['USER']->companyid;
}

try {
    // First check if company_course table exists
    $dbman = $DB->get_manager();
    $table = new xmldb_table('company_course');
    
    if ($dbman->table_exists($table)) {
        // Table exists, proceed with join query
        $sql = "SELECT c.id, c.fullname 
                FROM {course} c
                JOIN {company_course} cc ON c.id = cc.courseid
                WHERE cc.companyid = ? AND c.visible = 1
                ORDER BY c.fullname";
        $courses = $DB->get_records_sql($sql, [$company_id]);
    } else {
        // Fallback to getting all visible courses if company_course table doesn't exist
        $courses = $DB->get_records('course', ['visible' => 1], 'fullname', 'id, fullname');
    }

    if (debugging()) {
        echo '<div class="alert alert-info">Found ' . count($courses) . ' courses for company ID: ' . $company_id . '</div>';
    }

    $course_options = [];
    foreach ($courses as $course) {
        if ($course->id > 1) { // Skip site course
            $course_options[] = [
                'id' => $course->id,
                'name' => $course->fullname,
                'selected' => in_array($course->id, $selected_courses)
            ];
        }
    }
} catch (Exception $e) {
    if (debugging()) {
        echo '<div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div>';
    }
    // Fallback to empty course list
    $course_options = [];
}

// Check if we should show completion mode selector (only when multiple courses selected)
$show_completion_mode = count($selected_courses) > 1;

// Prepare template data
$data = [
    'selected_frameworks' => $selected_frameworks_data,
    'courses_options' => $course_options,
    'show_completion_mode' => $show_completion_mode,
    'is_mode_any' => ($completion_mode === 'any'),
    'is_mode_all' => ($completion_mode === 'all'),
    'resourceid' => $resource_id
];



// Output the page header
echo $OUTPUT->header();
echo $OUTPUT->render($tabs);

echo '<div class="card mb-3"><div class="card-body">';
echo '<div class="alert alert-warning"><i class="fa fa-clock-o" aria-hidden="true"></i> ' . get_string('sync_timing_notice', 'local_vanta') . '</div>';

// Render the completion sync rules template
echo $OUTPUT->render_from_template('local_vanta/training_sync_rules', $data);
$PAGE->requires->js_call_amd('local_vanta/training_sync_rules1', 'init');
echo '</div></div>'; // End card

// Output the page footer
echo $OUTPUT->footer(); 