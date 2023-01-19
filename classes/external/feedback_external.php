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

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_tipcoll\models\feedback;
use mod_tipcoll\tipcoll;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

class feedback_external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function feedback_parameters(): external_function_parameters {
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
    public static function feedback(int $cmid): array {
        self::validate_parameters(
            self::feedback_parameters(), [
                'cmid' => $cmid
            ]
        );

        $tipcoll = new tipcoll($cmid);
        $feedbackinstance = $tipcoll->get_feedback();

        $questions = [];
        $error = '';
        $success = false;

        if (!is_null($feedbackinstance)) {
            $success = true;
            $feedback = new feedback($feedbackinstance->cmid);
            $questions = $feedback->get_questions();
        } else {
            $error = 'FEEDBACK NOT FOUND';
        }

        return [
            'success' => $success,
            'error' => $error,
            'questions' => $questions
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function feedback_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
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
                                        'title' => new external_value(PARAM_TEXT, 'Response Title')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }
}