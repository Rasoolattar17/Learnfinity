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
 * Simple Widget main page
 *
 * @package    local_simplewidget
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('local/simplewidget:view', $context);

$PAGE->set_url('/local/simplewidget/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_simplewidget'));
$PAGE->set_heading(get_string('pluginname', 'local_simplewidget'));

echo $OUTPUT->header();

// Display welcome message.
echo html_writer::tag('h2', get_string('welcome', 'local_simplewidget'));
echo html_writer::tag('p', get_string('description', 'local_simplewidget'));

// Get user information.
$userinfo = local_simplewidget_get_user_info();
$stats = local_simplewidget_get_site_stats();

// Display widget content.
echo html_writer::start_tag('div', ['class' => 'simplewidget-container']);
echo html_writer::tag('h3', get_string('widget_title', 'local_simplewidget'));

// User information section.
echo html_writer::start_tag('div', ['class' => 'user-info']);
echo html_writer::tag('h4', get_string('user_info', 'local_simplewidget'));
echo html_writer::start_tag('ul');
echo html_writer::tag('li', 'Name: ' . $userinfo['fullname']);
echo html_writer::tag('li', 'Email: ' . $userinfo['email']);
echo html_writer::tag('li', 'Last Access: ' . $userinfo['lastaccess']);
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');

// Site statistics section.
echo html_writer::start_tag('div', ['class' => 'site-stats']);
echo html_writer::tag('h4', 'Site Statistics');
echo html_writer::start_tag('ul');
echo html_writer::tag('li', get_string('course_count', 'local_simplewidget') . ': ' . $stats['courses']);
echo html_writer::tag('li', get_string('user_count', 'local_simplewidget') . ': ' . $stats['users']);
echo html_writer::tag('li', get_string('current_time', 'local_simplewidget') . ': ' . $stats['current_time']);
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');

// Refresh button.
echo html_writer::start_tag('div', ['class' => 'refresh-section']);
$refreshurl = new moodle_url('/local/simplewidget/index.php');
echo html_writer::link($refreshurl, get_string('refresh', 'local_simplewidget'),
    ['class' => 'btn btn-primary']);
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
