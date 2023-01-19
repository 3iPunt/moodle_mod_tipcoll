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
 *
 * @author 2023 3iPunt <https://www.tresipunt.com/>
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
            FEEDBACK_CONTENT: '[data-region="feedback-content"]'
        };

        /**
         *
         */
        let SERVICES = {
            GET_FEEDBACK: 'mod_tipcoll_feedback'
        };

        /**
         *
         */
        let TEMPLATES = {
            FEEDBACK_CONTENT: 'mod_tipcoll/feedback_content',
            LOADING: 'core/overlay_loading'
        };

        /**
         * @constructor
         * @param {String} region
         * @param {Number} cmid
         */
        function FeedbackModal(region, cmid) {
            this.node = $(region);

            var identifier = this.node.find(REGIONS.FEEDBACK_CONTENT);

            console.log(identifier);

            Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
                var request_footer = {
                    methodname: SERVICES.GET_FEEDBACK,
                    args: {
                        cmid: cmid
                    }
                };
                Ajax.call([request_footer])[0].done(function(response) {
                    var template = TEMPLATES.FEEDBACK_CONTENT;
                    Templates.render(template, response).done(function(html, js) {
                        identifier.html(html);
                        Templates.runTemplateJS(js);
                    });
                }).fail(Notification.exception);
            });

        }

        /** @type {jQuery} The jQuery node for the region. */
        FeedbackModal.prototype.node = null;

        return {
            /**
             * @param {String} region
             * @param {Number} cmid
             * @return {FeedbackModal}
             */
            initFeedbackModal: function (region, cmid) {
                return new FeedbackModal(region, cmid);
            }
        };
    }
);