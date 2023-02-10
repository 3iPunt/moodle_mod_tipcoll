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
            QUESTION: '[data-region="mod-tipcoll-question"]',
            RESPONSE: '[data-region="mod-tipcoll-response"]',
        };

        /**
         *
         */
        let INPUT = {
            GROUP_NAME: '#form-create-group #name'
        };

        /**
         *
         */
        let ACTION = {
            CREATE_GROUP: '[data-action="create-group"]',
            DELETE_GROUP: '[data-action="delete-group"]',
            ASSIGN_GROUP: '[data-action="assign-group"]',
            GROUP_NAME: '[data-input="group-name-create"]',
        };

        /**
         *
         */
        let SERVICES = {
            CREATE: 'mod_tipcoll_group_create',
            DELETE: 'mod_tipcoll_group_delete',
            ASSIGN: 'mod_tipcoll_group_assign',
        };

        /**
         * @param {String} region
         * @param {Number} cmid
         *
         * @constructor
         */
        function Groups(region, cmid) {
            this.node = $(region);
            this.cmid = cmid;
            this.node.find(INPUT.GROUP_NAME).on('keyup', this.onGroupNameChange.bind(this));
            this.node.find(INPUT.GROUP_NAME).on('key', this.onGroupNameChange.bind(this));
            this.node.find(ACTION.CREATE_GROUP).on('click', this.onCreateClick.bind(this));
            this.node.find(ACTION.DELETE_GROUP).on('click', this.onDeleteClick.bind(this));
            this.node.find(ACTION.ASSIGN_GROUP).on('change', this.onAssignClick.bind(this));
        }

        Groups.prototype.onGroupNameChange = function (e) {
            if (e.which === 13 || e.which === 32) {
                location.reload();
            }
            let groupname = $(INPUT.GROUP_NAME).val();
            groupname = groupname.trim();
            if (groupname.length > 3) {
                this.node.find(ACTION.CREATE_GROUP).removeAttr('disabled');
            } else {
                this.node.find(ACTION.CREATE_GROUP).attr('disabled', true);
            }
        };

        Groups.prototype.onCreateClick = function (e) {
            this.node.find(ACTION.CREATE_GROUP).attr('disabled', true);

            let groupname = $(INPUT.GROUP_NAME).val();
            groupname = groupname.trim();

            const request = {
                methodname: SERVICES.CREATE,
                args: {
                    cmid: this.cmid,
                    name: groupname
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

        Groups.prototype.onDeleteClick = function (e) {
            $(e.currentTarget).attr('disabled', true);
            let gid = $(e.currentTarget).data('groupid');

            const request = {
                methodname: SERVICES.DELETE,
                args: {
                    cmid: this.cmid,
                    groupid: parseInt(gid)
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

        Groups.prototype.onAssignClick = function (e) {
            let gid = $(e.currentTarget).val();
            let uid = $(e.currentTarget).data('userid');

            const request = {
                methodname: SERVICES.ASSIGN,
                args: {
                    cmid: this.cmid,
                    userid: parseInt(uid),
                    groupid: parseInt(gid)
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

        /** @type {jQuery} The jQuery node for the region. */
        Groups.prototype.node = null;

        return {
            /**
             * Init
             *
             * @param {String} region
             * @param {Number} cmid
             *
             * @return {Groups}
             */
            initGroups: function(region, cmid) {
                return new Groups(region, cmid);
            }
        };
    }
);