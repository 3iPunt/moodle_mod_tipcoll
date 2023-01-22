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
        let ACTION = {
            START: '[data-action="start"]',
            SEND_RESPONSE: '[data-action="send_response"]',
            SELECT_RESPONSE: '[data-action="select_response"]',
            NAV_ITEM: '[data-region="nav-item"]',
        };

        /**
         *
         */
        let SERVICES = {
            RESPONSE: 'mod_tipcoll_response_question_feedback',
        };

        /**
         * @param {String} region
         * @param {Number} cmid
         *
         * @constructor
         */
        function Feedback(region, cmid) {
            this.node = $(region + '[data-id="' + cmid + '"]');
            this.cmid = cmid;
            this.setResponses();
            this.node.find(ACTION.START).on('click', this.onStartClick.bind(this));
            this.node.find(ACTION.NAV_ITEM).on('click', this.onStartClick.bind(this));
            this.node.find(ACTION.SELECT_RESPONSE).on('click', this.onSelectResponseClick.bind(this));
            this.node.find(ACTION.SEND_RESPONSE).on('click', this.onSendResponseClick.bind(this));
        }

        Feedback.prototype.setResponses = function (e) {
            let questions = this.node.find(REGIONS.QUESTION);
            let quizs = [];
            questions.each(function(index) {
                let quiz = $(this);
                let qid = quiz.data('questionid');
                let resps = quiz.find(REGIONS.RESPONSE);
                let responses = [];
                resps.each(function(index) {
                    let resp = $(this);
                    let rid = resp.data('responseid');
                    responses.push(rid);
                });
                let question = {
                    'id': qid,
                    'responses': responses,
                    'select' : null
                };
                quizs.push(question);
            });
            this.responses = quizs;
        };

        Feedback.prototype.updateStatus = function (e) {
            let responsesCurrent = this.responses;
            let questions = this.node.find(REGIONS.QUESTION);
            questions.each(function(index) {
                let quiz = $(this);
                let qid = quiz.data('questionid');
                let resps = quiz.find(REGIONS.RESPONSE);
                resps.each(function(index) {
                    let resp = $(this);
                    let rid = resp.data('responseid');
                    resp.removeClass('ok');
                    responsesCurrent.forEach(function (element, index, array) {
                        if (element.id === qid) {
                            if(element.select === rid ) {
                                resp.addClass('ok');
                            }
                        }
                    });
                });
            });
            let already = true;
            responsesCurrent.forEach(function (element, index, array) {
                if (element.select === null) {
                    already = false;
                    return true;
                }
            });
            if (already) {
                this.node.find(ACTION.SEND_RESPONSE).removeAttr('disabled');
            }
        };

        Feedback.prototype.onStartClick = function (e) {
            let sendButton = this.node.find(ACTION.SEND_RESPONSE + '[data-id="' + this.cmid + '"]');
            sendButton.show();
        };

        Feedback.prototype.onSelectResponseClick = function (e) {
            let rid = $(e.currentTarget).data('responseid');
            let qid = $(e.currentTarget).data('questionid');
            this.responses.forEach(function (element, index, array) {
                if(element.id === qid) {
                    element.select = rid;
                    $(e.currentTarget).addClass('ok');
                    return true;
                }
            });
            this.updateStatus();
        };

        Feedback.prototype.getResponse = function (e) {
            let response = [];
            this.responses.forEach(function (element, index, array) {
                response.push({'qid': element.id, 'rid' : element.select});
            });
            return response;
        };

        Feedback.prototype.onSendResponseClick = function (e) {
            this.node.find(ACTION.SEND_RESPONSE).attr('disabled');
            const response = this.getResponse();
            const request = {
                methodname: SERVICES.RESPONSE,
                args: {
                    cmid: this.cmid,
                    response: response
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
        Feedback.prototype.node = null;

        return {
            /**
             * Init
             *
             * @param {String} region
             * @param {Number} cmid
             *
             * @return {Feedback}
             */
            initFeedback: function(region, cmid) {
                return new Feedback(region, cmid);
            }
        };
    }
);