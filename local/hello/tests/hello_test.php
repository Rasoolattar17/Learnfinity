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
 * Tests for the Hello World plugin.
 *
 * @package    local_hello
 * @copyright  2025, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hello;

/**
 * Test cases for Hello World plugin functionality.
 *
 * @package    local_hello
 * @copyright  2025, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class hello_test extends \advanced_testcase {

    /**
     * Tests the greeting generation.
     *
     * @covers \local_hello\local_hello_generate_greeting
     */
    public function test_generate_greeting(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/hello/lib.php');

        // Test with default name.
        $greeting = local_hello_generate_greeting();
        $this->assertStringContainsString('Hello', $greeting);
        $this->assertStringContainsString('World', $greeting);

        // Test with custom name.
        $greeting = local_hello_generate_greeting('Moodle');
        $this->assertStringContainsString('Hello', $greeting);
        $this->assertStringContainsString('Moodle', $greeting);
    }

    /**
     * Tests the greeting generation.
     *
     * @covers \local_hello\local_hello_set_config
     */
    public function test_config_functions(): void {
        global $CFG;
        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/local/hello/lib.php');

        // Test setting and getting config.
        $result = local_hello_set_config('testkey', 'testvalue');
        $this->assertTrue($result);

        $config = local_hello_get_config();
        $this->assertInstanceOf('stdClass', $config);
    }

    /**
     * Tests the greeting generation.
     *
     * @covers \local_hello\get_string
     */
    public function test_language_strings(): void {
        $this->assertNotEmpty(get_string('pluginname', 'local_hello'));
        $this->assertNotEmpty(get_string('hello', 'local_hello'));
        $this->assertNotEmpty(get_string('world', 'local_hello'));
        $this->assertNotEmpty(get_string('greeting', 'local_hello'));
        $this->assertNotEmpty(get_string('welcome', 'local_hello'));
    }
}
