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
 * Main page for the test plugin.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/test_plugin:view', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/test_plugin/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_test_plugin'));
$PAGE->set_heading(get_string('pluginname', 'local_test_plugin'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo html_writer::div(
    get_string('welcome_message', 'local_test_plugin'),
    'alert alert-info'
);

echo $OUTPUT->heading(get_string('pluginname', 'local_test_plugin'));


echo $OUTPUT->heading(get_string('pluginname', 'local_test_plugin'));

echo html_writer::div(
    get_string('welcome_message', 'local_test_plugin'),
    'alert alert-info'
);

echo $OUTPUT->heading(get_string('pluginname', 'local_test_plugin'));

echo html_writer::div(
    get_string('welcome_message', 'local_test_plugin'),
    'alert alert-info'
);


echo $OUTPUT->heading(get_string('pluginname', 'local_test_plugin'));

echo html_writer::div(
    get_string('welcome_message', 'local_test_plugin'),
    'alert alert-info'
);

echo $OUTPUT->heading(get_string('pluginname', 'local_test_plugin'));




echo $OUTPUT->footer();
