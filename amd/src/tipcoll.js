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
 * @package
 * @author  2023 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable no-unused-vars */
/* eslint-disable no-console */

define([
        'jquery',
        'core/str',
        'core/ajax',
        'core/templates'
    ], function ($, Str, Ajax, Templates) {
        "use strict";


        /**
         *
         */
        let REGIONS = {
            CONTENT: '[data-region="mod_tipcoll-content"]',
        };

        /**
         *
         */
        let SERVICES = {
            GET_CONTENT: 'mod_tipcoll_get_content',
        };

        /**
         *
         */
        let TEMPLATES = {
            MODULE_CONTENT: 'mod_tipcoll/content_component',
            LOADING: 'core/overlay_loading'
        };

        /**
         * @constructor
         */
        function TipColl() {

            let activities = $('.course-content [data-region="mod_tipcoll"]');

            activities.each(function(index) {

                let node = $(this);
                let cmid = node.data('cmid');

                let identifier = node.find(REGIONS.CONTENT);

                Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
                    let request_footer = {
                        methodname: SERVICES.GET_CONTENT,
                        args: {
                            cmid: cmid
                        }
                    };
                    Ajax.call([request_footer])[0].done(function(response) {
                        if (response.success) {
                            let template = TEMPLATES.MODULE_CONTENT;
                            Templates.render(template, response).done(function(html, js) {
                                identifier.html(html);
                                Templates.runTemplateJS(js);
                            });
                        } else {
                            console.log(response);
                        }
                    }).fail(function(response) {
                        console.log(response);
                    });
                });
            });

        }

        /** @type {jQuery} The jQuery node for the region. */
        TipColl.prototype.node = null;

        return {
            /**
             * Init
             *
             * @return {TipColl}
             */
            initTipColl: function() {
                return new TipColl();
            }
        };
    }
);