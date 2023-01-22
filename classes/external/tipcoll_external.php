<?php
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
 * @package     mod_tipcoll
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright   3iPunt <https://www.tresipunt.com/>
 */

namespace mod_tipcoll\external;

use context_course;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_tipcoll\tipcoll;
use mod_tipcoll\tipcoll_user;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

class tipcoll_external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function content_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module ID')
            )
        );
    }

    /**
     * Feedback.
     *
     * @param int $cmid
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function content(int $cmid): array {
        global $USER;

        self::validate_parameters(
            self::content_parameters(), [
                'cmid' => $cmid
            ]
        );

        $tipcoll = new tipcoll($cmid);
        $feedbackinstance = $tipcoll->get_feedback()->get_instance();

        $error = '';
        $success = false;
        $data = [
                'deadline' => null,
                'cmid' => null,
                'is_teacher' => false,
                'is_student' => false,
                'teacher_data' => [
                        'enabled' => false,
                        'deadline' => '',
                        'remain' => 0,
                        'already' => 0,
                        'url' => '#',
                        'can_create_groups' => false
                ],
                'status_feedback' => [
                        'enabled' => false,
                        'deadline' => '',
                        'questions' => []
                ],
                'status_completed' => [
                        'enabled' => false,
                        'deadline' => ''
                ],
                'status_deadline' => [
                        'enabled' => false,
                        'has_group' => false,
                        'groupname' => '',
                        'participants' => []
                ]
        ];

        try {
            if (!is_null($feedbackinstance)) {
                $success = true;
                $data['cmid'] = $tipcoll->get_cmid();
                $data['title'] = $tipcoll->get_title();
                $data['description'] = $tipcoll->get_description();
                $tipcolluser = new tipcoll_user($tipcoll, $USER);
                if ($tipcolluser->is_student()) {
                    $data['is_student'] = true;
                    switch ($tipcolluser->get_status()) {
                        case 'deadline':
                            $status = [];
                            $status['enabled'] = true;
                            $status['has_group'] = $tipcolluser->has_group();
                            $status['groupname'] = $tipcolluser->get_groupname();
                            $status['participants'] = $tipcolluser->get_members();
                            $data['status_deadline'] = $status;
                            break;
                        case 'completed':
                            $status = [];
                            $status['enabled'] = true;
                            $status['deadline'] = $tipcoll->get_deadline();
                            $data['status_completed'] = $status;
                            break;
                        case 'feedback':
                            $feedback = $tipcoll->get_feedback();
                            $status = [];
                            $status['enabled'] = true;
                            $status['deadline'] = $tipcoll->get_deadline();
                            $status['questions'] = $feedback->get_questions();
                            $data['status_feedback'] = $status;
                            break;
                    }
                }
                if ($tipcolluser->is_teacher()) {
                    $data['is_teacher'] = true;
                    $status = [];
                    $status['enabled'] = true;
                    $status['deadline'] = $tipcoll->get_deadline();
                    $status['already'] = $tipcoll->get_feedback()->get_already();
                    $status['remain'] = $tipcoll->get_feedback()->get_remain();
                    $status['url'] = $tipcoll->get_group_url();
                    $status['can_create_groups'] = $tipcoll->can_create_groups();
                    $data['teacher_data'] = $status;
                }

            } else {
                $error = 'FEEDBACK NOT FOUND';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error,
            'data' => $data
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function content_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
                'data' => new external_single_structure(
                    array(
                    'cmid' =>  new external_value(PARAM_INT, 'Course Module ID'),
                    'title' =>  new external_value(PARAM_TEXT, 'Title'),
                    'is_student' => new external_value(PARAM_BOOL, 'Is Student?'),
                    'is_teacher' => new external_value(PARAM_BOOL, 'Is Teacher?'),
                    'description' =>  new external_value(PARAM_RAW, 'Course Module Intro'),
                    'teacher_data' => new external_single_structure(
                    array(
                        'enabled' => new external_value(PARAM_BOOL, 'Enabled?'),
                        'can_create_groups' => new external_value(PARAM_BOOL, 'Can Create Group?'),
                        'deadline' => new external_value(PARAM_TEXT, 'Deadline'),
                        'remain' => new external_value(PARAM_INT, 'Users remain to be answered'),
                        'already' => new external_value(PARAM_INT, 'Users have answered'),
                        'url' => new external_value(PARAM_RAW, 'URL')
                    ), '', VALUE_OPTIONAL),
                    'status_feedback' => new external_single_structure(
                    array(
                       'enabled' => new external_value(PARAM_BOOL, 'Enabled?'),
                       'deadline' => new external_value(PARAM_TEXT, 'Deadline'),
                       'questions' => new external_multiple_structure(
                           new external_single_structure(
                           array(
                               'id'       => new external_value(PARAM_INT, 'Question ID'),
                               'order' => new external_value(PARAM_INT, 'Question Order'),
                               'title' => new external_value(PARAM_TEXT, 'Question Title'),
                               'responses' => new external_multiple_structure(
                                   new external_single_structure(
                                       array(
                                               'id'       => new external_value(PARAM_RAW, 'Response ID'),
                                               'order' => new external_value(PARAM_INT, 'Response Order'),
                                               'questionid' => new external_value(PARAM_INT, 'Question ID'),
                                               'title' => new external_value(PARAM_TEXT, 'Response Title')
                                       )
                                   )
                               )
                           )
                           )
                       )
                    ), '', VALUE_OPTIONAL),
                    'status_completed' => new external_single_structure(
                     array(
                        'enabled' => new external_value(PARAM_BOOL, 'Enabled?'),
                        'deadline' => new external_value(PARAM_TEXT, 'Deadline')
                     ), '', VALUE_OPTIONAL),
                    'status_deadline' => new external_single_structure(
                    array(
                        'enabled' => new external_value(PARAM_BOOL, 'Enabled?'),
                        'has_group' => new external_value(PARAM_BOOL, 'User has group?'),
                        'groupname' => new external_value(PARAM_TEXT, 'Group Name'),
                        'participants' => new external_multiple_structure(
                        new external_single_structure(
                        array(
                            'picture' => new external_value(PARAM_RAW, 'Picture URL'),
                            'name' => new external_value(PARAM_TEXT, 'Participant FullName')
                            )
                        )
                        )
                    )
                    ), '', VALUE_OPTIONAL)
                )
            )
        );
    }
}