/* eslint-disable no-console */
/* eslint-disable max-len */
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
 * JavaScript for the Vanta logs page.
 *
 * @module     local_vanta/logs
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/str'
], function($, ajax, notification, templates, str) {
    "use strict";

    var logs = {
        filters: {
            useremail: '',
            courseid: 0,
            status: '',
            fromdate: '',
            todate: ''
        },
        page: 0,
        perpage: 10,

        init: function() {
            // Initialize filter values from form
            this.filters.useremail = $('#vanta-logs-filter-useremail').val();
            this.filters.courseid = parseInt($('#vanta-logs-filter-courseid').val()) || 0;
            this.filters.status = $('#vanta-logs-filter-status').val();
            this.filters.fromdate = $('#vanta-logs-filter-fromdate').val();
            this.filters.todate = $('#vanta-logs-filter-todate').val();

            // Bind event handlers
            this.bindEvents();

            // Don't call loadLogs() here since the server already rendered the initial data
            // loadLogs() will only be called when filters are applied or pagination is used
        },
        bindEvents: function() {
            var self = this;

            // Filter form submission
            $('#vanta-logs-filter-form').on('submit', function(e) {
                e.preventDefault();

                // Get current form values
                var fromdate = $('#vanta-logs-filter-fromdate').val();
                var todate = $('#vanta-logs-filter-todate').val();

                // Validate date range
                if (fromdate && todate) {
                    var fromTimestamp = new Date(fromdate).getTime();
                    var toTimestamp = new Date(todate).getTime();

                    if (toTimestamp < fromTimestamp) {
                        // Clear any existing validation classes and messages
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').remove();

                        // Add invalid class to the to date field
                        $('#vanta-logs-filter-todate').addClass('is-invalid');

                        // Get error message and add to field
                        str.get_string('date_validation_error', 'local_vanta').then(function(errorStr) {
                            // Remove any existing error messages for this field
                            $('#vanta-logs-filter-todate').siblings('.invalid-feedback').remove();

                            // Add error message after the input field
                            $('#vanta-logs-filter-todate').after('<div class="invalid-feedback">' + errorStr + '</div>');
                        }).catch(function() {
                            // Fallback error message if string fetch fails
                            $('#vanta-logs-filter-todate').siblings('.invalid-feedback').remove();
                            $('#vanta-logs-filter-todate').after('<div class="invalid-feedback">To date cannot be earlier than from date.</div>');
                        });
                        return; // Prevent form submission
                    }
                }

                // Remove any validation errors on successful validation
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                self.filters.useremail = $('#vanta-logs-filter-useremail').val();
                self.filters.courseid = parseInt($('#vanta-logs-filter-courseid').val()) || 0;
                self.filters.status = $('#vanta-logs-filter-status').val();
                self.filters.fromdate = fromdate;
                self.filters.todate = todate;
                self.page = 0; // Reset to first page
                self.loadLogs();
            });

            // Reset filters
            $('#vanta-logs-filter-reset').on('click', function(e) {
                e.preventDefault();
                $('#vanta-logs-filter-form')[0].reset();

                // Clear validation errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                self.filters = {
                    useremail: '',
                    courseid: 0,
                    status: '',
                    fromdate: '',
                    todate: ''
                };
                self.page = 0;
                self.loadLogs();
            });

            // Pagination
            $(document).on('click', '.vanta-logs-pagination .page-link', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page !== undefined) {
                    self.page = parseInt(page);
                    self.loadLogs();
                }
            });
        },

        loadLogs: function() {
            var self = this;

            // Show loading state
            $('#vanta-logs-loading').removeClass('d-none');
            $('#vanta-logs-content').addClass('d-none');

            var promise = ajax.call([{
                methodname: 'local_vanta_get_logs',
                args: {
                    page: self.page,
                    perpage: self.perpage,
                    filters: self.filters
                }
            }])[0];

            promise.done(function(response) {
                // Update only the table content and pagination, not the entire container
                var data = {
                    logs: response.logs,
                    has_logs: response.logs.length > 0,
                    total_logs: response.total,
                    showing_from: response.showing_from,
                    showing_to: response.showing_to,
                    has_pagination: response.total > self.perpage
                };

                // Add pagination data if needed
                if (data.has_pagination) {
                    data.pages = [];
                    var totalPages = Math.ceil(response.total / self.perpage);

                    // Previous page
                    if (self.page > 0) {
                        data.pages.push({
                            number: self.page - 1,
                            text: '«',
                            active: false
                        });
                    }

                    // Page numbers
                    for (var i = 0; i < totalPages; i++) {
                        data.pages.push({
                            number: i,
                            text: (i + 1).toString(),
                            active: i === self.page
                        });
                    }

                    // Next page
                    if (self.page < totalPages - 1) {
                        data.pages.push({
                            number: self.page + 1,
                            text: '»',
                            active: false
                        });
                    }
                }

                // Update the content without changing the overall structure
                if (data.has_logs) {
                    // Update the showing entries text
                    $('.card-header small').html(
                        'Showing entries: ' + data.showing_from + ' - ' + data.showing_to + ' of ' + data.total_logs
                    );

                    // Update table body
                    var tbody = $('#vanta-logs-display-table tbody');
                    tbody.empty();

                    data.logs.forEach(function(log) {
                        var statusBadge = '';
                        if (log.status_success) {
                            statusBadge = '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Success</span>';
                        } else if (log.status_error) {
                            statusBadge = '<span class="badge badge-danger"><i class="fa fa-times-circle"></i> Error</span>';
                        } else if (log.status_info) {
                            statusBadge = '<span class="badge badge-info"><i class="fa fa-info-circle"></i> Info</span>';
                        } else if (log.status_skipped) {
                            statusBadge = '<span class="badge badge-secondary"><i class="fa fa-minus-circle"></i> Skipped</span>';
                        } else {
                            statusBadge = '<span class="badge badge-light"><i class="fa fa-question-circle"></i> Unknown</span>';
                        }

                        var row = '<tr>' +
                            '<td>' + log.useremail + '</td>' +
                            '<td>' + log.coursename + '</td>' +
                            '<td data-sort="' + log.syncedon + '">' + log.syncedon_formatted + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td><a href="' + log.view_url + '" class="btn btn-sm btn-primary" title="View Details"><i class="fa fa-eye"></i> View</a></td>' +
                            '</tr>';
                        tbody.append(row);
                    });

                    // Remove any existing pagination first
                    $('.card-body .pagination').parent().remove();
                    $('.card-body .vanta-logs-pagination').parent().remove();

                    // Add new pagination if needed
                    if (data.has_pagination) {
                        var paginationHtml = '<nav aria-label="Pagination"><ul class="pagination justify-content-center vanta-logs-pagination">';
                        data.pages.forEach(function(page) {
                            var activeClass = page.active ? ' active' : '';
                            paginationHtml += '<li class="page-item' + activeClass + '">' +
                                '<a class="page-link" href="#" data-page="' + page.number + '">' + page.text + '</a>' +
                                '</li>';
                        });
                        paginationHtml += '</ul></nav>';

                        $('.card-body').append('<div class="mt-3">' + paginationHtml + '</div>');
                    }

                    // Show the table if it was hidden
                    $('#vanta-logs-display-table').closest('.table-responsive').show();
                    $('.card').show();

                } else {
                    // No logs found - show empty state
                    $('.card-header small').html('No entries found');
                    var tbody = $('#vanta-logs-display-table tbody');
                    tbody.html('<tr><td colspan="5" class="text-center">No logs found matching the current filters.</td></tr>');

                    // Remove pagination for empty results
                    $('.card-body .pagination').parent().remove();
                    $('.card-body .vanta-logs-pagination').parent().remove();
                }

                // Hide loading and show content
                $('#vanta-logs-loading').addClass('d-none');
                $('#vanta-logs-content').removeClass('d-none');

            }).fail(notification.exception);
        }
    };

    return {
        init: function() {
            logs.init();
        }
    };
});