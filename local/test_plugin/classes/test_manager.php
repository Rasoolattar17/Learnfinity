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
 * Test manager class.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_test_plugin;

/**
 * Test manager class for handling test operations.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_manager {

    /** @var int The user ID */
    private int $userid;

    /** @var \moodle_database The database instance */
    private \moodle_database $db;

    /**
     * Constructor for the test manager.
     *
     * @param int $userid The user ID
     */
    public function __construct(int $userid) {
        global $DB;
        $this->userid = $userid;
        $this->db = $DB;
    }

    /**
     * Get test data for the user.
     *
     * @return array<int, \stdClass> The test data
     */
    public function get_test_data(): array {
        $records = $this->db->get_records(
            'local_test_plugin_data',
            ['userid' => $this->userid],
            'timecreated DESC'
        );

        return array_values($records);
    }

    /**
     * Create a new test record.
     *
     * @param string $name The name
     * @param string $description The description
     * @return int|false The record ID or false on failure
     */
    public function create_test_record(string $name, string $description = ''): int|false {
        $record = new \stdClass();
        $record->userid = $this->userid;
        $record->name = $name;
        $record->description = $description;
        $record->timecreated = time();
        $record->timemodified = time();

        return $this->db->insert_record('local_test_plugin_data', $record);
    }

    /**
     * Update an existing test record.
     *
     * @param int $id The record ID
     * @param string $name The name
     * @param string $description The description
     * @return bool Success status
     */
    public function update_test_record(int $id, string $name, string $description = ''): bool {
        $record = new \stdClass();
        $record->id = $id;
        $record->name = $name;
        $record->description = $description;
        $record->timemodified = time();

        return $this->db->update_record('local_test_plugin_data', $record);
    }

    /**
     * Delete a test record.
     *
     * @param int $id The record ID
     * @return bool Success status
     */
    public function delete_test_record(int $id): bool {
        return $this->db->delete_records(
            'local_test_plugin_data',
            ['id' => $id, 'userid' => $this->userid]
        );
    }
}
