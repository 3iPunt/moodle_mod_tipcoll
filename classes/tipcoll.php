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
 * Class tipcoll
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll;

use cm_info;
use dml_exception;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

/**
 * Class tipcoll
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tipcoll {

    const TABLE = ['tipcoll'];


    /** @var stdClass Course */
    protected $course;

    /** @var cm_info Course Module */
    protected $cm;

    /**
     * constructor.
     *
     * @param int $cmid
     * @throws moodle_exception
     */
    public function __construct(int $cmid) {
        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);
    }

    /**
     * Get Activity by num
     *
     * @param int $num
     * @return false|mixed|stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_activity(int $num) {
        global $DB;
        $instanccoll = $DB->get_record('tipcoll', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $cmdata = $instanccoll->cmdata;
        $cmdata = json_decode($cmdata);
        if (isset($cmdata->$num) && isset($cmdata->$num->id)) {
            $item = $cmdata->$num;
            list($course, $cm) = get_course_and_cm_from_cmid($item->id);
            $instance = $DB->get_record($item->type, ['id' => $cm->instance], '*', MUST_EXIST);
            $instance->cmid = $item->id;
            return $instance;
        } else {
            debugging('Activity ' . $num . ': NOT FOUND');
            return null;
        }
    }

    /**
     * Get feedback
     *
     * @return false|mixed|stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_feedback() {
        global $DB;
        $instanccoll = $DB->get_record('tipcoll', ['id' => $this->cm->instance], '*', MUST_EXIST);
        if (isset($instanccoll->feedbackid)) {
            $feedbackcmid = $instanccoll->feedbackid;
            list($course, $cm) = get_course_and_cm_from_cmid($feedbackcmid);
            $instance = $DB->get_record('feedback', ['id' => $cm->instance], '*', MUST_EXIST);
            $instance->cmid = $feedbackcmid;
            return $instance;
        } else {
            debugging('Feedback NOT FOUND');
            return null;
        }
    }

}
