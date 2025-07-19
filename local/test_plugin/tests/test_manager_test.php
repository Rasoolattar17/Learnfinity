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
 * Test manager test class.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_test_plugin\test_manager
 */

namespace local_test_plugin;

/**
 * Test manager test class.
 *
 * @package    local_test_plugin
 * @copyright  2024 Your Name <your.email@moodle.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_test_plugin\test_manager
 */
final class test_manager_test extends \advanced_testcase {

    /**
     * Test creating a test manager instance.
     *
     * @covers \local_test_plugin\test_manager::__construct
     * @return void
     */
    public function test_constructor(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_test_plugin\test_manager($user->id);

        $this->assertInstanceOf(\local_test_plugin\test_manager::class, $manager);
    }

    /**
     * Test creating a test record.
     *
     * @covers \local_test_plugin\test_manager::create_test_record
     * @return void
     */
    public function test_create_test_record(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_test_plugin\test_manager($user->id);

        $name = 'Test Record';
        $description = 'Test Description';

        $recordid = $manager->create_test_record($name, $description);

        $this->assertIsInt($recordid);
        $this->assertGreaterThan(0, $recordid);
    }

    /**
     * Test getting test data.
     *
     * @covers \local_test_plugin\test_manager::get_test_data
     * @return void
     */
    public function test_get_test_data(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_test_plugin\test_manager($user->id);

        // Create some test records.
        $recordid1 = $manager->create_test_record('Record 1', 'Description 1');
        $recordid2 = $manager->create_test_record('Record 2', 'Description 2');

        $data = $manager->get_test_data();

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        // Check that both records exist in the data.
        $names = array_column($data, 'name');
        $this->assertContains('Record 1', $names);
        $this->assertContains('Record 2', $names);

        // Check that both descriptions exist in the data.
        $descriptions = array_column($data, 'description');
        $this->assertContains('Description 1', $descriptions);
        $this->assertContains('Description 2', $descriptions);
    }

    /**
     * Test updating a test record.
     *
     * @covers \local_test_plugin\test_manager::update_test_record
     * @return void
     */
    public function test_update_test_record(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_test_plugin\test_manager($user->id);

        $recordid = $manager->create_test_record('Original Name', 'Original Description');

        $result = $manager->update_test_record($recordid, 'Updated Name', 'Updated Description');

        $this->assertTrue($result);

        $data = $manager->get_test_data();
        $this->assertCount(1, $data);
        $this->assertEquals('Updated Name', $data[0]->name);
        $this->assertEquals('Updated Description', $data[0]->description);
    }

    /**
     * Test deleting a test record.
     *
     * @covers \local_test_plugin\test_manager::delete_test_record
     * @return void
     */
    public function test_delete_test_record(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $manager = new \local_test_plugin\test_manager($user->id);

        $recordid = $manager->create_test_record('Test Record', 'Test Description');

        $result = $manager->delete_test_record($recordid);

        $this->assertTrue($result);

        $data = $manager->get_test_data();
        $this->assertCount(0, $data);
    }
}
