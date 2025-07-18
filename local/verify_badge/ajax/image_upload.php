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
 * Handles AJAX image upload for badge verification.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/composer/jwtsecure.php');
defined('MOODLE_INTERNAL') || die();

require_login();

global $DB;
$renamedfilename = $_POST['badge-logo'];
$renamedfilename = explode('.', $renamedfilename);
$courseid  = jwtsecure::Decode($_POST['courseid']);

$id = $DB->get_field('local_verify_badge_details', 'id', ['course_id' => $courseid]);
$badgeimageeexists = $DB->get_field('local_verify_badge_details', 'badge_image', ['id' => $id]);

$time = time();

if (isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $filesize1 = $_FILES['file']['size'];
    $filetmp1 = $_FILES['file']['tmp_name'];
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = 'badge_logo_' . $id . '_' . $time . '.' . $extension;

    if (move_uploaded_file($filetmp1, $CFG->dataroot . '/uploads/verifybadge/' . $filename)) {

        $updateattachment = new stdClass();
        $updateattachment->id = $id;
        $updateattachment->badge_image = $filename;
        $update = $DB->update_record('local_verify_badge_details', $updateattachment);
        if ($update) {
            echo 'Success';
        } else {
            echo 'Error updating database';
        }
    } else {
        echo 'Error uploading file';
    }
}
