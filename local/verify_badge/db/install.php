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

function xmldb_local_verify_badge_install() {
    global $CFG;
    $dir = $CFG->dataroot . '/uploads';
    if (!is_dir($dir)) {
        if (PHP_OS == 'Linux') {
            // That means system is linux folder.
            mkdir($CFG->dataroot . '/uploads');
            mkdir($CFG->dataroot . '/uploads/verifybadge');
        } else {
            // That means creating folder in Windows System.
            mkdir($CFG->dataroot . '\\uploads');
            mkdir($CFG->dataroot . '\\uploads\\verifybadge');
        }
    } else {
        if (PHP_OS == 'Linux') {
            // That means system is linux folder.
            mkdir($CFG->dataroot . '/uploads/verifybadge');
            mkdir($CFG->dataroot . '/uploads/verifybadge/documents');
        } else {
            // That means creating folder in Windows System.
            mkdir($CFG->dataroot . '\\uploads\\verifybadge');
            mkdir($CFG->dataroot . '\\uploads\\verifybadge\\documents');
        }
    }
}
