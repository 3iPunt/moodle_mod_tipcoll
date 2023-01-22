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
        let ACTION = {
            CREATE_GROUP: '[data-action="filter-participant"]',
        };

        /**
         * @param {String} region
         * @param {Number} cmid
         *
         * @constructor
         */
        function Filters(region, cmid) {
            this.node = $(region);
            this.cmid = cmid;
            console.log('Filters...');
        }

        /** @type {jQuery} The jQuery node for the region. */
        Filters.prototype.node = null;

        return {
            /**
             * Init
             *
             * @param {String} region
             * @param {Number} cmid
             *
             * @return {Filters}
             */
            initFilters: function(region, cmid) {
                return new Filters(region, cmid);
            }
        };
    }
);