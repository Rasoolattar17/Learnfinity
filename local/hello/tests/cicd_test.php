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
 * CI/CD Test for Hello plugin.
 *
 * @package    local_hello
 * @copyright  2025, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hello;


/**
 * Test class for CI/CD functionality.
 *
 * @package    local_hello
 * @copyright  2025, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cicd_test extends \advanced_testcase {

    /**
     * Test that the CI/CD status function works correctly.
     *
     * @covers ::local_hello_get_cicd_status
     * @covers ::get_string
     */
    public function test_cicd_status(): void {
        $this->resetAfterTest();

        // Test the CI/CD status function.
        $status = \local_hello_get_cicd_status();
        $this->assertEquals('CI/CD Test Successful!', $status);

        // Test that the string exists in language file.
        $this->assertEquals('CI/CD Test Successful!', \get_string('ci_cd_test', 'local_hello'));
    }

    /**
     * Test that the greeting function works with updated strings.
     *
     * @covers ::local_hello_generate_greeting
     * @covers ::get_string
     */
    public function test_updated_greeting(): void {
        $this->resetAfterTest();

        // Test greeting with a name.
        $greeting = \local_hello_generate_greeting('Test User');
        $this->assertStringContainsString('Test User', $greeting);
        $this->assertStringContainsString('Welcome to the updated plugin!', $greeting);

        // Test greeting without a name (should use 'World').
        $greeting = \local_hello_generate_greeting('');
        $this->assertStringContainsString('World', $greeting);
    }
}
