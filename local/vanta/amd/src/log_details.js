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
 * JavaScript for the Vanta log details page.
 *
 * @module     local_vanta/log_details
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    
    /**
     * Module initialization.
     */
    function init() {
        setupCopyButtons();
    }
    
    /**
     * Add copy buttons to JSON payload sections
     */
    function setupCopyButtons() {
        // Add copy button to each pre element
        $('.card-body pre').each(function() {
            const $pre = $(this);
            const $card = $pre.closest('.card-body');
            
            // Create copy button
            const $button = $('<button>')
                .addClass('btn btn-sm btn-outline-secondary float-right copy-payload')
                .attr('title', 'Copy to clipboard')
                .html('<i class="fa fa-copy"></i> Copy');
            
            // Add button to card
            $card.prepend($button);
            
            // Add click handler
            $button.on('click', function(e) {
                e.preventDefault();
                
                // Copy pre content to clipboard
                const text = $pre.text();
                navigator.clipboard.writeText(text).then(function() {
                    // Success - change button text temporarily
                    const $btn = $(e.currentTarget);
                    const originalHtml = $btn.html();
                    
                    $btn.html('<i class="fa fa-check"></i> Copied!');
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2000);
                });
            });
        });
    }
    
    return {
        init: init
    };
}); 