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
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/composer/jwtsecure.php');
require_once($CFG->dirroot . '/local/verify_badge/lib.php');
require_once($CFG->dirroot . '/blocks/st_social_share/lib.php');

defined('MOODLE_INTERNAL') || die();
$courseid = $_GET['id'];

global $DB;
require_login();
$PAGE->set_url(new moodle_url('/local/verify_badge/index.php'));
$PAGE->set_context(context_system::instance());

$PAGE->set_pagelayout('develop');
$PAGE->set_title(get_string('verify_badge', 'local_verify_badge'));

$badgedetails = $DB->get_record('local_verify_badge_details', array('course_id' => jwtsecure::decode($courseid)));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/verify_badge/style/index.css'));

if ($badgedetails->badge_image) {
    $imageexists = true;
    $imagepath = $CFG->wwwroot . '/secure.php?file=verifybadge/' . $badgedetails->badge_image;
} else {
    $imageexists = false;
}

if ($badgedetails->title) {
    $badgetitle = $badgedetails->title;
}

if ($badgedetails->badge_text) {
    $badgetext = $badgedetails->badge_text;
}

if ($badgedetails->badge_link) {
    $badgelink = $badgedetails->badge_link;
}
if ($badgedetails->description) {
    $description = $badgedetails->description;
}

if ($badgedetails->issuing_organization) {
    $issuingorganization = $badgedetails->issuing_organization;
}

if ($badgedetails->org_link) {
    $orglink = $badgedetails->org_link;
}

if ($badgedetails->tags) {
    $tags = $badgedetails->tags;
}

if ($badgedetails->skills) {
    $skills = $badgedetails->skills;
}

$hash = array(
    'title' => $badgetitle,
    'badgetext' => $badgetext,
    'badgelink' => $badgelink,
    'description' => $description,
    'issueorg' => $issuingorganization,
    'orglink' => $orglink,
    'tags' => $tags,
    'skills' => $skills,
    'extracontent' => $badgedetails->extra_content,
    'imageexists' => $imageexists,
    'imagepath' => $imagepath,
    'courseid' => $courseid,
    'verifysample' => $CFG->wwwroot . '/local/verify_badge/pix/sample.png'
);

echo $OUTPUT->header();
echo $OUTPUT->render(block_st_social_share_tabs(['id' => $courseid], 'verify_badge'));
echo $OUTPUT->render_from_template('local_verify_badge/index', $hash);
$PAGE->requires->js_call_amd('local_verify_badge/index', 'init', array(
    'courseid' => $courseid,
    'imageexists' => $imageexists,
));

echo $OUTPUT->footer();
