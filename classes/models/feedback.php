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
use core_user;
use dml_exception;
use invalid_parameter_exception;
use mod_feedback\external\feedback_item_exporter;
use mod_feedback_completion;
use mod_feedback_external;
use mod_feedback_responses_table;
use mod_feedback_structure;
use mod_tipcoll\tipcoll;
use mod_tipcoll\tipcoll_user;
use moodle_exception;
use moodle_url;
use stdClass;
use tool_brickfield\local\areas\core_course\fullname;

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

    const COLOUR = [
            '#7FBF3F', '#BF3F3F', '#B36B0E', '#0EB36B', '#190EB3', '#5F93AE', '#5B3FBF'
    ];

    /** @var stdClass Course */
    protected $course;

    /** @var cm_info Course Module */
    protected $cm;

    /** @var stdClass Instance */
    protected $instance;

    /** @var tipcoll TipColl */
    protected $tipcoll;

    /** @var stdClass[] Participants */
    protected $participants;

    /** @var int Remain */
    protected $remain;

    /**
     * constructor.
     *
     * @param stdClass $instance
     * @param tipcoll $tipcoll
     * @throws moodle_exception
     */
    public function __construct(stdClass $instance, tipcoll $tipcoll) {
        list($this->course, $this->cm) = get_course_and_cm_from_cmid($instance->cmid);
        $this->instance = $instance;
        $this->tipcoll = $tipcoll;
    }

    /**
     * Get Instance.
     *
     * @return stdClass
     */
    public function get_instance(): stdClass {
        return $this->instance;
    }

    /**
     * Get CM.
     *
     * @return cm_info
     */
    public function get_cm(): cm_info {
        return $this->cm;
    }

    /**
     * Get Instance Id.
     *
     * @return int
     */
    public function get_id(): int {
        return $this->instance->id;
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
                $item->cmid = $this->tipcoll->get_cmid();
                $item->order = $order;
                $item->title = $q->name;
                $item->color = self::COLOUR[$order];
                $item->responses = $this->get_responses($q->presentation, $q->id);
                $item->infilter = $this->in_filter($q->id);
                $items[] = $item;
                $order ++;
            }
        }
        return $items;
    }

    /**
     * Get Responses.
     *
     * @param string $presentation
     * @param int $qid
     * @return array
     * @throws coding_exception
     */
    public function get_responses(string $presentation, int $qid): array {
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
            $item->questionid = $qid;
            $item->order = $order;
            $item->title = $resp;
            $item->selected = $this->resp_selected($qid, $order);
            $items[] = $item;
            $order ++;
        }
        return $items;
    }

    /**
     * Response selected.
     *
     * @param int $qid
     * @param int $rid
     * @return bool
     * @throws coding_exception
     */
    protected function resp_selected(int $qid, int $rid): bool {
        $rfilter = optional_param('qid-' . $qid, null, PARAM_INT);
        return $rfilter === $rid;
    }

    /**
     * In filter?
     *
     * @param int $qid
     * @return bool
     * @throws coding_exception
     */
    protected function in_filter(int $qid) {
        $value = optional_param('qid-' . $qid, null, PARAM_INT);
        return !empty($value);
    }

    /**
     * Get Already.
     *
     * @return int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_already(): int {
        return count($this->get_participants());
    }

    /**
     * Get Participants.
     *
     * @return stdClass[]
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_participants(): array {
        if (is_null($this->participants)) {
            $this->set_participants();
        }
        return $this->participants;
    }

    /**
     * Set Participants.
     *
     * @throws dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function set_participants(){
        global $DB, $PAGE;
        $completedtotal =  $DB->get_records('feedback_completed', ['feedback' => $this->get_id()]);
        $userids = [];
        foreach ($completedtotal as $comp) {
            $userids[] = $comp->userid;
        }
        $userids = array_unique($userids);
        $this->participants = [];
        foreach ($userids as $userid) {
            $user = core_user::get_user($userid);
            $tipcolluser = new tipcoll_user($this->tipcoll, $user);
            $feedbackuser = new feedback_user($this, $tipcolluser);
            $userpicture = new \user_picture($user);
            $userpicture->size = 1;
            $participant = new stdClass();
            $participant->id = $userid;
            $participant->picture = $userpicture->get_url($PAGE)->out(false);
            $participant->fullname = fullname($user);
            $participant->responses = $feedbackuser->get_responses();
            $participant->groups = $tipcolluser->get_groups();
            $participant->ingroup = !empty($tipcolluser->get_group());
            $this->participants[] = $participant;
        }
    }

    /**
     * Get Remain.
     *
     * @return int
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public function get_remain(): int {
        if (is_null($this->remain)) {
            $this->set_remain();
        }
        return $this->remain;
    }

    /**
     * Set Already.
     *
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    protected function set_remain() {
        $res = mod_feedback_external::get_non_respondents($this->get_id());
        $this->remain = $res['total'];
    }

}
