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
 * Database upgrade script for local_vanta
 *
 * @package    local_vanta
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade local_vanta plugin.
 *
 * @param int $oldversion The old version of the plugin
 * @return bool
 */
function xmldb_local_vanta_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024052000) {
        // Define table local_vanta_api_credentials to be created.
        $table = new xmldb_table('local_vanta_api_credentials');

        // Adding fields to table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('client_secret', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('resource_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('grant_type', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('company_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('created_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('modified_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table.
        $table->add_index('company_idx', XMLDB_INDEX_NOTUNIQUE, array('company_id'));
        $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, array('status'));
        $table->add_index('deleted_idx', XMLDB_INDEX_NOTUNIQUE, array('deleted'));

        // Create the table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Migrate existing plugin configuration to the new table if any exists.
        $client_id = get_config('local_vanta', 'client_id');
        $client_secret = get_config('local_vanta', 'client_secret');
        $resource_id = get_config('local_vanta', 'resource_id');
        
        if (!empty($client_id) && !empty($client_secret)) {
            $record = new stdClass();
            $record->client_id = $client_id;
            $record->client_secret = $client_secret;
            $record->resource_id = $resource_id ?? '';
            $record->scope = 'connectors.self:write-resource';
            $record->grant_type = 'client_credentials';
            $record->company_id = 0;
            $record->status = 1;
            $record->deleted = 0;
            $record->created_by = 0;
            $record->created_on = time();
            $record->modified_by = 0;
            $record->modified_on = time();
            
            $DB->insert_record('local_vanta_api_credentials', $record);
            
            // Clean up old config values
            unset_config('client_id', 'local_vanta');
            unset_config('client_secret', 'local_vanta');
            unset_config('resource_id', 'local_vanta');
        }

        // Local_vanta savepoint reached.
        upgrade_plugin_savepoint(true, 2024052000, 'local', 'vanta');
    }

    if ($oldversion < 2025052102) {
        // Get DB manager.
        $dbman = $DB->get_manager();
    
        // Define the table and the new field.
        $table = new xmldb_table('local_vanta_api_credentials');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
    
        // Conditionally add the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // Update existing records after the field is added.
        $DB->set_field('local_vanta_api_credentials', 'name', 'Vanta API Credentials');
    
        // Save upgrade point.
        upgrade_plugin_savepoint(true, 2025052102, 'local', 'vanta');
    }
    
    if ($oldversion < 2025052104) {
        // Step 1: Define and create the table structure
        $table = new xmldb_table('local_vanta_training_sync_rules');

        // Check if the table already exists - if not, create it
        if (!$dbman->table_exists($table)) {
            // Adding fields to table
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('vanta_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('resource_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('frameworks', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('courses', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('completion_mode', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'any');
            $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('created_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('modified_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            // Adding keys to table
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Create the table
            $dbman->create_table($table);
        }
        
        // Step 2: Migrate existing configuration data to the new table
        $frameworks = get_config('local_vanta', 'selected_frameworks');
        $courses = get_config('local_vanta', 'selected_courses');
        $completion_mode = get_config('local_vanta', 'completion_mode') ?: 'any';
        $resource_id = get_config('local_vanta', 'resource_id');
        
        if (!empty($frameworks) || !empty($courses)) {
            $record = new stdClass();
            $record->vanta_id = 'default';
            $record->resource_id = $resource_id ?? '';
            $record->frameworks = $frameworks ?? '';
            $record->courses = $courses ?? '';
            $record->completion_mode = $completion_mode;
            $record->created_by = 0;
            $record->created_on = time();
            $record->modified_by = 0;
            $record->modified_on = time();
            
            $DB->insert_record('local_vanta_training_sync_rules', $record);
            
            // Clean up old config values
            unset_config('selected_frameworks', 'local_vanta');
            unset_config('selected_courses', 'local_vanta');
            unset_config('completion_mode', 'local_vanta');
            // Keep resource_id as it might be used elsewhere
        }

        // Local_vanta savepoint reached.
        upgrade_plugin_savepoint(true, 2025052104, 'local', 'vanta');
    }
    
    if ($oldversion < 2025052105) {
        // Define table local_vanta_sync_logs to be created
        $table = new xmldb_table('local_vanta_sync_logs');

        // Check if the table already exists - if not, create it
        if (!$dbman->table_exists($table)) {
            // Adding fields to table
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('useremail', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('coursename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('syncedon', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('request_payload', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
            $table->add_field('response_payload', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
            $table->add_field('error_message', XMLDB_TYPE_TEXT, null, null, null, null, null);

            // Adding keys to table
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Adding indexes to table
            $table->add_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, array('userid'));
            $table->add_index('courseid_idx', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
            $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, array('status'));
            $table->add_index('syncedon_idx', XMLDB_INDEX_NOTUNIQUE, array('syncedon'));

            // Create the table
            $dbman->create_table($table);
        }

        // Local_vanta savepoint reached
        upgrade_plugin_savepoint(true, 2025052105, 'local', 'vanta');
    }

    if ($oldversion < 2025052106) {
        // Define table local_vanta_sync_queue to be created
        $table = new xmldb_table('local_vanta_sync_queue');

        // Check if the table already exists - if not, create it
        if (!$dbman->table_exists($table)) {
            // Adding fields to table
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('company_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('reason', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'pending');
            $table->add_field('queued_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('processed_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('attempts', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('error_message', XMLDB_TYPE_TEXT, null, null, null, null, null);

            // Adding keys to table
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Adding indexes to table
            $table->add_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $table->add_index('courseid_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
            $table->add_index('company_id_idx', XMLDB_INDEX_NOTUNIQUE, ['company_id']);
            $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $table->add_index('queued_at_idx', XMLDB_INDEX_NOTUNIQUE, ['queued_at']);

            // Create the table
            $dbman->create_table($table);
        }

        // Local_vanta savepoint reached
        upgrade_plugin_savepoint(true, 2025052106, 'local', 'vanta');
    }

    if ($oldversion < 2025052107) {
        // Define table local_vanta_regeneration_queue to be created
        $table = new xmldb_table('local_vanta_regeneration_queue');

        // Check if the table already exists - if not, create it
        if (!$dbman->table_exists($table)) {
            // Adding fields to table
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('company_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('reason', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'pending');
            $table->add_field('queued_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('started_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('completed_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('error_message', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('triggered_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

            // Adding keys to table
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Adding indexes to table
            $table->add_index('company_id_idx', XMLDB_INDEX_NOTUNIQUE, ['company_id']);
            $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $table->add_index('queued_at_idx', XMLDB_INDEX_NOTUNIQUE, ['queued_at']);

            // Create the table
            $dbman->create_table($table);
        }

        // Local_vanta savepoint reached
        upgrade_plugin_savepoint(true, 2025052107, 'local', 'vanta');
    }

    return true;
} 