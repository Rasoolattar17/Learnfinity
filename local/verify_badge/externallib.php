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
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/verify_badge/lib.php');

require_login();

class local_verify_badge_external extends external_api {

    /**
     * This function is used to add and verify badge details
     * @return bool $response
     * @author Rasool.
     */
    public static function add_badge_details_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_RAW, 'courseid'),
            'title' => new external_value(PARAM_TEXT, 'title'),
            'badgetext' => new external_value(PARAM_TEXT, 'badgetext'),
            'badgelink' => new external_value(PARAM_TEXT, 'badgelink'),
            'badgedescription' => new external_value(PARAM_TEXT, 'badgedescription'),
            'issueorg' => new external_value(PARAM_TEXT, 'issueorg'),
            'orglink' => new external_value(PARAM_TEXT, 'orglink'),
            'tags' => new external_value(PARAM_TEXT, 'tags'),
            'skills' => new external_value(PARAM_TEXT, 'skills'),
            'extracontent' => new external_value(PARAM_RAW, 'extracontent'),
            'badgeimage' => new external_value(PARAM_TEXT, 'badgeimage', VALUE_DEFAULT, null),
        ));
    }

    public static function add_badge_details() {
        return local_verify_badge_add_badge_details(func_get_args());
    }

    public static function add_badge_details_returns() {
        return new external_value(PARAM_TEXT, 'response');
    }

    /**
     * This function is used to delete the image.
     * @return array $response
     * @author Rasool
     */

    public static function delete_image_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_RAW, ''),
        ));
    }

    public static function delete_image($courseid) {
        return local_verify_badge_delete_image($courseid);
    }

    public static function delete_image_returns() {
        return new external_value(PARAM_TEXT, 'deleted succeessfully');
    }
}
