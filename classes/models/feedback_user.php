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
 * Class feedback_user
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
use mod_feedback_completion;
use mod_feedback_external;
use mod_feedback_structure;
use mod_tipcoll\tipcoll_user;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

/**
 * Class feedback_user
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_user {

    /** @var feedback Feedback */
    protected $feedback;

    /** @var tipcoll_user Tip Coll User */
    protected $tipcolluser;

    /** @var mod_feedback_completion Completion */
    protected $completion;

    /** @var stdClass Last Completed */
    protected $lastcompleted;

    /**
     * constructor.
     *
     * @param feedback $feedback
     * @param tipcoll_user $tipcolluser
     * @throws coding_exception
     */
    public function __construct(feedback $feedback, tipcoll_user $tipcolluser) {
        $this->feedback = $feedback;
        $this->tipcolluser = $tipcolluser;
        $this->set_completion();
    }

    /**
     * Set Completion.
     *
     * @throws coding_exception
     */
    public function set_completion() {
        $this->completion = new mod_feedback_completion(
                $this->feedback->get_instance(),
                $this->feedback->get_cm(),
                $this->feedback->get_cm()->course,
                false,
                null,
                null,
                $this->tipcolluser->get_user()->id
        );
    }

    /**
     * Get Find Lastd Completed.
     *
     * @return stdClass|null
     */
    public function get_find_last_completed(): ?stdClass {
        if (is_null($this->lastcompleted)) {
            $this->set_last_completed();
        }
        return $this->lastcompleted === false ? null : $this->lastcompleted;
    }

    /**
     * Set Last Completed.
     */
    protected function set_last_completed() {
        $this->lastcompleted = $this->completion->find_last_completed();
    }

    /**
     * Is completed?
     *
     * @return bool
     */
    public function is_completed(): bool {
        return !is_null($this->get_find_last_completed());
    }

    /**
     * Get Responses.
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_responses(): array {
        $items = [];
        $lastcompleted = $this->get_find_last_completed();
        if (isset($lastcompleted)) {
            foreach ($this->feedback->get_questions() as $question) {
                $item = new stdClass();
                $item->qorder = $question->order;
                $item->color = $question->color;
                $item->qtitle = $question->title;
                $item->response = $this->get_response($question->id, $lastcompleted, $question->responses);
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Get Response value.
     *
     * @param int $qid
     * @param stdClass $lastcompleted
     * @param stdClass[] $questionresponses
     * @return string
     * @throws dml_exception
     */
    public function get_response(int $qid, stdClass $lastcompleted, array $questionresponses): string {
        global $DB;
        $response = $DB->get_record('feedback_value',
                ['completed' => $lastcompleted->id, 'item' => $qid]);
        $value = !empty($response) ? (int)$response->value : null;
        if (!is_null($value)) {
            foreach ($questionresponses as $resp) {
                if ($resp->order === $value) {
                    return $resp->title;
                }
            }
        }
        return '-';
    }

}
