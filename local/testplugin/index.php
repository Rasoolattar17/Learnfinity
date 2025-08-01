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
 * Test plugin main page
 *
 * @package    local_testplugin
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/testplugin/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_testplugin'));
$PAGE->set_heading(get_string('pluginname', 'local_testplugin'));

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('welcome', 'local_testplugin'));
echo html_writer::tag('p', get_string('description', 'local_testplugin'));

// Display plugin status.
if (local_testplugin_is_enabled()) {
    echo $OUTPUT->notification(get_string('enabled', 'local_testplugin'), 'success');
} else {
    echo $OUTPUT->notification('Plugin is disabled', 'warning');
}

// Simple test functionality.
echo html_writer::start_tag('div', ['class' => 'testplugin-content']);
echo html_writer::tag('h3', 'Plugin Information');
echo html_writer::start_tag('ul');
echo html_writer::tag('li', 'Plugin Name: ' . get_string('pluginname', 'local_testplugin'));
echo html_writer::tag('li', 'Version: 1.0.0');
echo html_writer::tag('li', 'Status: ' . (local_testplugin_is_enabled() ? 'Enabled' : 'Disabled'));
echo html_writer::tag('li', 'Current Time: ' . date('Y-m-d H:i:s'));
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
