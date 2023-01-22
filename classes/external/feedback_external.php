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
use mod_feedback_completion;
use mod_tipcoll\tipcoll;
use mod_tipcoll\tipcoll_user;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

class feedback_external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function response_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'Feedback ID'),
                'response' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'qid' => new external_value(PARAM_INT, 'Question ID'),
                            'rid' => new external_value(PARAM_INT, 'Response ID selected')
                        )
                    )
                )
            )
        );
    }

    /**
     * response.
     *
     * @param int $cmid
     * @param array $response
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function response(int $cmid, array $response): array {
        global $USER;
        self::validate_parameters(
            self::response_parameters(), [
                'cmid' => $cmid,
                'response' => $response
            ]
        );
        $tipcoll = new tipcoll($cmid);
        $feedbackinstance = $tipcoll->get_feedback()->get_instance();

        $error = '';
        $success = false;
        try {
            if (!is_null($feedbackinstance)) {
                $tipcolluser = new tipcoll_user($tipcoll, $USER);
                if ($tipcolluser->is_student()) {
                    $completion = new mod_feedback_completion(
                            $feedbackinstance, $tipcoll->get_feedback()->get_cm(), $tipcoll->get_course()->id);
                    $answers = new stdClass();
                    $answers->id = $tipcoll->get_feedback()->get_id();
                    $answers->courseid = $tipcoll->get_course()->id;
                    $answers->gopage = 3;
                    $answers->lastpage = 3;
                    $answers->startitempos = 3;
                    $answers->lastitempos = 3;
                    $answers->savevalues = 'Submit your answers';
                    foreach ($response as $resp) {
                        $quiz = 'multichoice_' . $resp['qid'];
                        $answers->{$quiz} = $resp['rid'];
                    }
                    $completion->save_response_tmp($answers);
                    $completion->save_response();
                    $success = true;
                } else {
                    $error = 'USER IS NOT STUDENT';
                }
            } else {
                $error = 'FEEDBACK NOT FOUND';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function response_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message')
            )
        );
    }
}
