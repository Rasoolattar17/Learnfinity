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
 * Library functions for the Verify Badge local plugin.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/composer/jwtsecure.php');
require_once($CFG->dirroot . '/local/classes/generic.php');
require_once($CFG->libdir . '/succeed_date_lib.php');

/**
 * Adds or updates badge details in the database.
 *
 * @param array $parameter Array of badge details in the following order:
 *   [0] string $courseid (encoded),
 *   [1] string $title,
 *   [2] string $badge_text,
 *   [3] string $badge_link,
 *   [4] string $description,
 *   [5] string $issuing_organization,
 *   [6] string $org_link,
 *   [7] string $tags,
 *   [8] string $skills,
 *   [9] string $extra_content,
 *   [10] string|null $badge_image (optional)
 * @return int 1 on success, 0 on failure.
 */
function local_verify_badge_add_badge_details($parameter) {
    try {
        global $DB;

        $courseid = $parameter[0];

        if ($courseid) {
            $courseid = (int)jwtsecure::Decode($courseid);
        }

        $badge = new stdClass();
        $badge->course_id = $courseid;
        $badge->title = $parameter[1];
        $badge->badge_text = $parameter[2];
        $badge->badge_link = $parameter[3];
        $badge->description = $parameter[4];
        $badge->issuing_organization = $parameter[5];
        $badge->org_link = $parameter[6];
        $badge->tags = $parameter[7];
        $badge->skills = $parameter[8];
        $badge->extra_content = $parameter[9];

        if ($DB->record_exists('local_verify_badge_details', ['course_id' => $courseid])) {
            $id = $DB->get_field('local_verify_badge_details', 'id', ['course_id' => $courseid]);
            if ($parameter[10] == null) {
                $badgeimage = $DB->get_field(
                    'local_verify_badge_details',
                    'badge_image',
                    ['course_id' => $courseid]
                );
            }

            $badge->id = $id;
            $badge->modified_by = $_SESSION['USER']->id;
            $badge->modified_on = time();
            $badge->badge_image = $badgeimage;

            $result = $DB->update_record('local_verify_badge_details', $badge);

            return $result ? 1 : 0;
        } else {
            $badge->created_by = $_SESSION['USER']->id;
            $badge->created_on = time();
            $badge->badge_image = $parameter[10];
            $result = $DB->insert_record('local_verify_badge_details', $badge);

            return $result ? 1 : 0;
        }
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
}

/**
 * Deletes the badge image for a given course.
 *
 * @param string $courseid The course ID (encoded).
 * @return bool True on success, false on failure.
 */
function local_verify_badge_delete_image($courseid) {
    try {
        global $CFG, $DB;

        if ($courseid) {
            $courseid = (int)jwtsecure::Decode($courseid);
        }

        $select = $DB->get_field('local_verify_badge_details', 'badge_image', ['course_id' => $courseid]);
        unlink($CFG->dataroot . "/uploads/verifybadge/" . $select);
        $id = $DB->get_field('local_verify_badge_details', 'id', ['course_id' => $courseid]);

        $update = new stdClass();

        $update->courseid = $courseid;
        $update->id = $id;
        $update->badge_image = null;

        return $DB->update_record('local_verify_badge_details', $update);
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
}
