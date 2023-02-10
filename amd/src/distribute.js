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
        let ACTION = {
            DISTRIBUTE: '[data-action="auto-distribute"]',
            NUM_SELECT: '[data-action="num-select"]'
        };

        /**
         *
         */
        let SERVICES = {
            DISTRIBUTE: 'mod_tipcoll_distribute',
        };

        /**
         *
         */
        let REGIONS = {
            VIEW_PAGE: '[data-region="mod_tipcoll-view-page"',
            PARTICIPANTS: '[data-region="participants"',
        };

        /**
         * @param {String} region
         * @param {Number} cmid
         *
         * @constructor
         */
        function Distribute(region, cmid) {
            this.node = $(region);
            this.cmid = cmid;
            this.node.find(ACTION.DISTRIBUTE).on('click', this.onDistributeClick.bind(this));
        }

        Distribute.prototype.onDistributeClick = function (e) {
            let usersid = this.getUsersID();
            let numusers = this.node.find(ACTION.NUM_SELECT).val();
            numusers = parseInt(numusers);
            const request = {
                methodname: SERVICES.DISTRIBUTE,
                args: {
                    cmid: this.cmid,
                    usersid: usersid,
                    numusers: numusers
                }
            };
            Ajax.call([request])[0].done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    console.log(response);
                }
            }).fail(function(fail) {
                console.log(fail);
            });
        };

        Distribute.prototype.getUsersID = function (e) {
            this.nodeParticipants = $('[data-region="mod_tipcoll-view-page"] [data-region="participants"]');
            let userids = [];
            this.nodeParticipants.find('.participant').each(function(index) {
                let participant = $(this);
                let userid = participant.data('userid');
                userids.push(userid);
            });
            return userids.toString();
        };

        /** @type {jQuery} The jQuery node for the region. */
        Distribute.prototype.node = null;

        return {
            /**
             * Init
             *
             * @param {String} region
             * @param {Number} cmid
             *
             * @return {Distribute}
             */
            initDistribute: function(region, cmid) {
                return new Distribute(region, cmid);
            }
        };
    }
);