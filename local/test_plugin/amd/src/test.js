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
 * JavaScript module for the test plugin.
 *
 * @module local_test_plugin/test
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    /**
     * Module initialization.
     *
     * @param {string} selector The selector for the test elements
     */
    var init = function(selector) {
        $(selector).on('click', '.test-action', function(e) {
            e.preventDefault();
            var action = $(this).data('action');
            var id = $(this).data('id');
            
            performAction(action, id);
        });
    };

    /**
     * Perform a test action.
     *
     * @param {string} action The action to perform
     * @param {number} id The record ID
     */
    var performAction = function(action, id) {
        var promises = Ajax.call([{
            methodname: 'local_test_plugin_' + action,
            args: {id: id},
            done: function(response) {
                Notification.alert(
                    'Success',
                    'Action completed successfully',
                    'OK'
                );
                location.reload();
            },
            fail: function(error) {
                Notification.alert(
                    'Error',
                    'An error occurred: ' + error.message,
                    'OK'
                );
            }
        }]);
    };

    /**
     * Get test data via AJAX.
     *
     * @param {number} userid The user ID
     * @returns {Promise} The AJAX promise
     */
    var getTestData = function(userid) {
        return Ajax.call([{
            methodname: 'local_test_plugin_get_data',
            args: {userid: userid}
        }])[0];
    };

    return {
        init: init,
        performAction: performAction,
        getTestData: getTestData
    };
}); 