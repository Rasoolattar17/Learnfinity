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
 * Standard Ajax wrapper for Moodle. It calls the central Ajax script,
 * which can call any existing webservice using the current session.
 * In addition, it can batch multiple requests and return multiple responses.
 *
 * @module     local_ic_meetings/zoom
 * @copyright  2022, Succeed Technologies <platforms@succeedtech.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';

/**
 * @param {object} element
 * @param {object} container
 */
 export const zoomIn = (element, container) => {
    $('.st-popup-zoom').remove();

    var image = false,
        video = false,
        type = '',
        src = '';

    switch (element.target.tagName.toLowerCase()) {
        case 'img':
            image = true;
            src = $(element.currentTarget).attr('src');
            break;
        case 'video':
            video = true;
            type = $(element.currentTarget).find('source').attr('type');
            src = $(element.currentTarget).find('source').attr('src');
            break;
    }

    var hash = {
        src: src,
        image: image,
        video: video,
        type: type
    };

    Templates.render('local_verify_badge/zoom', hash)
    .then(function(html) {
        container.append(html);
        $('.st-popup-zoom').modal();
        return;
    })
    .fail(function() {
        // Error.
    });
};
