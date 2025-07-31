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
 * This page is used to swipe the users which need to be enrolled/unenrolled.
 * It also allow you to satisfy course mandatory status and course due date for them.
 * It will give information about the payment
 * @package   local_swap_list
 * @author    Sruthy
 * @copyright 2023, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
defined('MOODLE_INTERNAL') || die();

require_login();
$PAGE->set_url(new moodle_url('/local/swap_list/index.php'));
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('pluginname', 'local_swap_list'));

$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/swap_list/style/swap_list.css'));

$PAGE->set_pagelayout('learn');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_swap_list/index', []);
$PAGE->requires->js_call_amd('local_swap_list/index', 'init');
echo $OUTPUT->footer();
