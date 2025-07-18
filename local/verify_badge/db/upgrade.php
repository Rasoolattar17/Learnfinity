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
 * Upgrade script for the Verify Badge local plugin.
 *
 * @package   local_verify_badge
 * @author    Rasool
 * @copyright 2024, Succeed Technologies <platforms@succeedtech.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Executes database upgrades for the Verify Badge plugin.
 *
 * @param int $oldversion The old plugin version.
 * @return bool True on success.
 */
function xmldb_local_verify_badge_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024032002) {

        // Define table local_verify_badge_details to be created.
        $table = new xmldb_table('local_verify_badge_details');

        // Adding fields to table local_verify_badge_details.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('badge_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('badge_link', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('cert_id', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('issuing_organization', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('tags', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('skills', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_verify_badge_details.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_verify_badge_details.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032002, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032003) {

        // Define field created_by to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'skills');

        // Conditionally launch add field created_by.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field created_on to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('created_on', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_by');

        // Conditionally launch add field created_on.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field modified_by to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_on');

        // Conditionally launch add field modified_by.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field modified_on to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('modified_on', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'modified_by');

        // Conditionally launch add field modified_on.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032003, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032004) {

        // Define field org_link to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('org_link', XMLDB_TYPE_TEXT, null, null, null, null, null, 'modified_on');

        // Conditionally launch add field org_link.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032004, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032005) {

        // Define field user_id to be dropped from local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('user_id');

        // Conditionally launch drop field user_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field cert_id to be dropped from local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('cert_id');

        // Conditionally launch drop field cert_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032005, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032006) {

        // Define table local_verifybadge_uservisit to be created.
        $table = new xmldb_table('local_verifybadge_uservisit');

        // Adding fields to table local_verifybadge_uservisit.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('page_url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('ip_address', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('browser', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('visited_on', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('session_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_verifybadge_uservisit.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_verifybadge_uservisit.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032006, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032007) {

        // Changing type of field session_id on table local_verifybadge_uservisit to text.
        $table = new xmldb_table('local_verifybadge_uservisit');
        $field = new xmldb_field('session_id', XMLDB_TYPE_TEXT, null, null, null, null, null, 'visited_on');

        // Launch change of type for field session_id.
        $dbman->change_field_type($table, $field);

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032007, 'local', 'verify_badge');
    }

    if ($oldversion < 2024032008) {

        // Define field extra_content to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('extra_content', XMLDB_TYPE_TEXT, null, null, null, null, null, 'org_link');

        // Conditionally launch add field extra_content.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field badge_image to be added to local_verify_badge_details.
        $table = new xmldb_table('local_verify_badge_details');
        $field = new xmldb_field('badge_image', XMLDB_TYPE_TEXT, null, null, null, null, null, 'extra_content');

        // Conditionally launch add field badge_image.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Verify_badge savepoint reached.
        upgrade_plugin_savepoint(true, 2024032008, 'local', 'verify_badge');
    }

    return true;
}
