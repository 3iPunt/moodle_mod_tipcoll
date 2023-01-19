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
 * Class feedback
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\models;

use cm_info;
use coding_exception;
use dml_exception;
use mod_feedback\external\feedback_item_exporter;
use mod_feedback_external;
use mod_feedback_structure;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

/**
 * Class feedback
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback {

    /** @var stdClass Course */
    protected $course;

    /** @var cm_info Course Module */
    protected $cm;

    /** @var stdClass Instance */
    protected $instance;

    /**
     * constructor.
     *
     * @param int $cmid
     * @throws moodle_exception
     */
    public function __construct(int $cmid) {
        global $DB;
        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);
        $this->instance = $DB->get_record('feedback', ['id' => $this->cm->instance]);
    }

    /**
     * Get Questions.
     *
     * @return array
     * @throws coding_exception
     */
    public function get_questions(): array {
        $feedbackstructure = new mod_feedback_structure($this->instance, $this->cm, $this->course->id);
        $items = [];
        if ($questions = $feedbackstructure->get_items()) {
            $order = 1;
            foreach ($questions as $q) {
                $item = new stdClass();
                $item->id = $q->id;
                $item->order = $order;
                $item->title = $q->name;
                $item->responses = $this->get_responses($q->presentation, $q->id);
                $items[] = $item;
                $order ++;
            }
        }
        return $items;
    }

    /**
     * Get Responses.
     *
     * @return array
     */
    public function get_responses(string $presentation, $qid): array {
        $answers = str_replace('r>>>>>', '', $presentation);
        $answers = str_replace('<<<<<1', '', $answers);
        $answers = str_replace("\r\n", '', $answers);
        $answers = explode("|", $answers);

        $items = [];
        $order = 1;
        foreach ($answers as $answer) {
            $resp = trim($answer);
            $item = new stdClass();
            $item->id = $qid . '-' . $order;
            $item->order = $order;
            $item->title = $resp;
            $items[] = $item;
            $order ++;
        }
        return $items;
    }

}
