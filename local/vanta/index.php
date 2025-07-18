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
 * Admin configuration page for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tabobject;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/vanta/lib.php');
require_once($CFG->dirroot . '/lib/weblib.php'); // For tabobject class
defined('MOODLE_INTERNAL') || die();
// Security checks
require_login();
// $context = context_system::instance();
// require_capability('moodle/site:config', $context);
// $courseid = 2;
// $userid = 5;
// $result = \vanta_helper::handle_course_completion_full_sync($userid, $courseid);
// Get vanta_id parameter (optional)
$vanta_id = optional_param('vanta_id', '', PARAM_RAW);

// Page setup.
$url_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
$PAGE->set_url(new moodle_url('/local/vanta/index.php', $url_params));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('settings', 'local_vanta'));
$PAGE->set_heading(get_string('settings', 'local_vanta'));

$PAGE->set_pagelayout('administrator');

// Create navigation tabs using the helper function
$tab_params = !empty($vanta_id) ? ['vanta_id' => $vanta_id] : [];
$tabs = local_vanta_tabs($tab_params, 'settings');
$company_id = $_SESSION['USER']->companyid ?? 0;

$api_credentials = local_vanta_get_api_credentials($company_id);

// Setup for page rendering
$renderer = $PAGE->get_renderer('core');
$template_context = [
    "scope_value" => get_string('scope_value', 'local_vanta'),
    "grant_type_value" => get_string('grant_type_value', 'local_vanta'),
    "api_credentials" => $api_credentials,
    "name" => $api_credentials->name,
    "clientid" => $api_credentials->client_id,
    "clientsecret" => $api_credentials->client_secret,
    "resourceid" => $api_credentials->resource_id
];

// JS initialization
$PAGE->requires->js_call_amd('local_vanta/index', 'init');

// Output the page
echo $OUTPUT->header();
echo $OUTPUT->render($tabs);

echo '<div class="card mb-3"><div class="card-body">';
// Display information about Vanta integration
echo '<div class="alert alert-info mb-4">' . get_string('config_info', 'local_vanta') . '</div>';

// Render the configuration form
echo $renderer->render_from_template('local_vanta/index', $template_context);

echo '</div></div>'; // End card

// Output the page footer
echo $OUTPUT->footer(); 