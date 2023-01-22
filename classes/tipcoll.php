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
use coding_exception;
use course_enrolment_manager;
use dml_exception;
use mod_tipcoll\models\feedback;
use moodle_exception;
use moodle_url;
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

    /** @var stdClass Instance */
    protected $instance;

    /** @var feedback Feedback */
    protected $feedback;

    /**
     * constructor.
     *
     * @param int $cmid
     * @throws moodle_exception
     */
    public function __construct(int $cmid) {
        global $DB;
        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);
        $this->instance = $DB->get_record('tipcoll', array( 'id' => $this->cm->instance ));
    }

    /**
     * Get CM id.
     *
     */
    public function get_cmid(): int {
        return $this->cm->id;
    }

    /**
     * Get Course.
     *
     */
    public function get_course(): stdClass {
        return $this->course;
    }

    /**
     * Get Title.
     *
     * @throws coding_exception
     */
    public function get_title(): string {
        return get_string('welcome', 'mod_tipcoll');
    }

    /**
     * Get Deadline.
     *
     * @throws coding_exception
     */
    public function get_deadline(): string {
        return userdate(
                $this->get_deadline_timestamp(),
                get_string('strftimedate', 'core_langconfig')
        );
    }

    /**
     * Get Deadline Unixtime.
     *
     */
    public function get_deadline_timestamp(): string {
        return $this->instance->feedback_deadline;
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
     * @return feedback
     * @throws moodle_exception
     */
    public function get_feedback(): feedback {
        if (is_null($this->feedback)) {
            $this->set_feedback();
        }
        return $this->feedback;
    }

    /**
     * Set Feedback.
     *
     * @throws moodle_exception
     */
    protected function set_feedback() {
        global $DB;
        try {
            $instanccoll = $DB->get_record('tipcoll', ['id' => $this->cm->instance], '*', MUST_EXIST);
            if (isset($instanccoll->feedbackid)) {
                $feedbackcmid = $instanccoll->feedbackid;
                list($course, $cm) = get_course_and_cm_from_cmid($feedbackcmid);
                $instance = $DB->get_record('feedback', ['id' => $cm->instance], '*', MUST_EXIST);
                $instance->cmid = $feedbackcmid;
                $instance->section = $cm->section;
                $this->feedback = new feedback($instance, $this);
            } else {
                throw new moodle_exception('Feedback NOT FOUND');
            }
        } catch (moodle_exception $e) {
            throw new moodle_exception('Feedback NOT FOUND: ' . $e->getMessage());
        }
    }

    /**
     * Get Description.
     *
     * @throws dml_exception
     */
    public function get_description(): string {
        global $DB;
        $instance = $DB->get_record($this->cm->modname, ['id' => $this->cm->instance]);
        if (isset($instance)) {
            $introrewrite = file_rewrite_pluginfile_urls(
                $instance->intro,
                'pluginfile.php',
                $this->cm->context->id,
                'mod_' . $this->cm->modname,
                'intro',
                null);

            $desc = format_text($introrewrite, FORMAT_HTML, array('filter' => true));
            return empty($desc) ? '<p>...</p>' : $desc;
        } else {
            return '<p>...</p>';
        }
    }

    /**
     * Get Group URL.
     *
     * @return string
     * @throws moodle_exception
     */
    public function get_group_url(): string {
        $url = new moodle_url('/mod/tipcoll/view.php', ['id' => $this->course->id]);
        return $url->out(false);
    }

    /**
     * Can Create Groups?
     *
     * @return bool
     */
    public function can_create_groups(): bool {
        return $this->get_deadline_timestamp() >= time();
    }

    /**
     * Get Total Students.
     *
     * @return int
     * @throws dml_exception
     */
    public function get_total_students(): int {
        global $DB, $PAGE, $CFG;
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $role = $DB->get_record('role', array('shortname' => 'student'));
        $enrolmanager = new course_enrolment_manager($PAGE, $this->course, $instancefilter = null, $role->id,
                $searchfilter = '', $groupfilter = 0, $statusfilter = -1);
        $students = $enrolmanager->get_users('id', 'ASC', 0, 0);
        return count($students);
    }

}
