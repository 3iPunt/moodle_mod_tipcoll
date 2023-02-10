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
use core_user;
use course_enrolment_manager;
use course_modinfo;
use dml_exception;
use mod_tipcoll\factory\module;
use mod_tipcoll\models\feedback;
use mod_tipcoll\models\feedback_user;
use moodle_exception;
use moodle_url;
use section_info;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/lib/modinfolib.php');

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
     * Get Section.
     *
     * @return section_info|null
     */
    public function get_section(): ?section_info {
        return $this->cm->get_section_info();
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
     */
    public function get_activity(int $num) {
        global $DB;
        $cmdata = $this->get_activities();
        if (isset($cmdata->$num) && isset($cmdata->$num->id)) {
            try {
                $item = $cmdata->$num;
                list($course, $cm) = get_course_and_cm_from_cmid($item->id);
                $instance = $DB->get_record($item->type, ['id' => $cm->instance], '*', MUST_EXIST);
                $instance->cmid = $item->id;
                return $instance;
            } catch (moodle_exception $e) {
                debugging($e->getMessage());
                return null;
            }
        } else {
            debugging('Activity ' . $num . ': NOT FOUND');
            return null;
        }
    }

    /**
     * Get Activities.
     *
     * @return mixed
     */
    public function get_activities() {
        $cmdata = $this->instance->cmdata;
        $cmdata = json_decode($cmdata);
        return $cmdata;
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
     * Get Result URL.
     *
     * @return string
     * @throws moodle_exception
     */
    public function get_result_url(): string {
        $url = new moodle_url('/mod/feedback/show_entries.php', ['id' => $this->get_feedback()->get_cm()->id]);
        return $url->out(false);
    }

    /**
     * Get Group URL.
     *
     * @return string
     * @throws moodle_exception
     */
    public function get_group_url(): string {
        $url = new moodle_url('/mod/tipcoll/view.php', ['id' => $this->cm->id]);
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

    /**
     * Get Groups.
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_groups(): array {
        $items = [];
        $groups = groups_get_all_groups($this->course->id);
        foreach ($groups as $gr) {
            $idnumber = explode('_', $gr->idnumber);
            if ($idnumber[0] === 'tipcoll') {
                $groupcmid = (int)$idnumber[1];
                if ($groupcmid === $this->get_cmid()) {
                    $g = new stdClass();
                    $g->id = $gr->id;
                    $g->name = $gr->name;
                    $g->members = $this->get_members_group($gr->id);
                    $members = new moodle_url('/group/members.php', ['group' => $gr->id]);
                    $g->members_url = $members->out(false);
                    $edit = new moodle_url('/group/group.php?courseid=6&id=60', [
                            'id' => $gr->id,
                            'courseid' => $this->get_course()->id]
                    );
                    $g->edit_url = $edit->out(false);
                    $items[] = $g;
                }
            }
        }
        return $items;
    }

    /**
     * Get Members.
     *
     * @param int $groupid
     * @return stdClass[]
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_members_group(int $groupid): array {
        global $PAGE;
        $items = [];
        $members = groups_get_groups_members([$groupid]);
        foreach ($members as $member) {
            $user = core_user::get_user($member->id);
            $tipcolluser = new tipcoll_user($this, $user);
            $feedbackuser = new feedback_user($this->get_feedback(), $tipcolluser);
            $userpicture = new \user_picture($user);
            $userpicture->size = 1;
            $participant = new stdClass();
            $participant->id = $member->id;
            $participant->picture = $userpicture->get_url($PAGE)->out(false);
            $participant->fullname = fullname($user);
            $participant->responses = $feedbackuser->get_responses();
            $items[] = $participant;
        }
        return $items;
    }

    /**
     * Create Group.
     *
     * @param string $name
     * @return int
     * @throws moodle_exception
     */
    public function create_group(string $name): int {
        $data = new stdClass();
        $data->name = $name;
        $data->courseid = $this->course->id;
        $data->id = (int)groups_create_group($data);
        $data->idnumber = 'tipcoll_' . $this->get_cmid() . '_' . $data->id;
        $res = groups_update_group($data);
        $this->set_restriction_section();
        return $data->id;
    }

    /**
     * Delete Group.
     *
     * @param int $id
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function delete_group(int $id): bool {
        $res = groups_delete_group($id);
        $this->set_restriction_section();
        return $res;
    }

    /**
     * Set restriction section.
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function set_restriction_section() {
        global $DB;
        $section = $this->cm->get_section_info();
        $newsection = new stdClass();
        $newsection->id = $section->id;
        $newsection->course = $section->course;
        $newsection->name = $section->name;
        $newsection->summmary = $section->summary;
        $newsection->summaryformat = $section->summaryformat;
        $newsection->sequence = $section->sequence;
        $newsection->visible = $section->visible;
        $newsection->availability = json_encode($this->get_availability());
        $newsection->timemodified = time();
        $DB->update_record('course_sections', $newsection);
        course_modinfo::clear_instance_cache($this->get_course());
        rebuild_course_cache($this->get_course()->id);
    }

    /**
     * Set restriction activities.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function set_restriction_activities() {
        global $DB;
        $activities = $this->get_activities();
        foreach ($activities as $activity) {
            $record = $DB->get_record('course_modules', ['id' => $activity->id]);
            if ($record) {
                $record->availability = json_encode($this->get_availability_cm());
                $record->timemodified = time();
                $DB->update_record('course_modules', $record);
            }
        }
        course_modinfo::clear_instance_cache($this->get_course());
        rebuild_course_cache($this->get_course()->id);
    }

    /**
     * Get Availability Course Module.
     *
     * @return stdClass
     */
    public function get_availability_cm(): stdClass {
        $date = new stdClass();
        $date->type = 'date';
        $date->d = '>=';
        $date->t = (int)$this->get_deadline_timestamp();
        $rest[] = $date;
        $newavaliability = new stdClass();
        $newavaliability->op = '&';
        $newavaliability->c = $rest;
        $newavaliability->showc = [false];
        return $newavaliability;
    }

    /**
     * Get Availability Default Course Module.
     *
     * @param int $timestamp
     * @return stdClass
     */
    static public function get_availability_default_cm(int $timestamp): stdClass {
        $date = new stdClass();
        $date->type = 'date';
        $date->d = '>=';
        $date->t = $timestamp;
        $rest[] = $date;
        $newavaliability = new stdClass();
        $newavaliability->op = '&';
        $newavaliability->c = $rest;
        $newavaliability->showc = [false];
        return $newavaliability;
    }

    /**
     * Get Availability.
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_availability(): stdClass {
        $groupstipcoll = [];
        foreach ($this->get_groups() as $g) {
            $gr = new stdClass();
            $gr->type = 'group';
            $gr->id = (int)$g->id;
            $groupstipcoll[] = $gr;
        }
        $date = new stdClass();
        $date->type = 'date';
        $date->d = '<';
        $date->t = (int)$this->get_deadline_timestamp();
        $groupstipcoll[] = $date;

        $groupscond = new stdClass();
        $groupscond->op = '|';
        $groupscond->c = $groupstipcoll;
        $groups = [$groupscond];
        $newavaliability = new stdClass();
        $newavaliability->op = '&';
        $newavaliability->c = $groups;
        $newavaliability->showc = [false];
        return $newavaliability;
    }

    /**
     * Get Availability Default.
     *
     * @param int $time
     * @return stdClass
     */
    static public function get_availability_default(int $time): stdClass {
        $groupstipcoll = [];
        $date = new stdClass();
        $date->type = 'date';
        $date->d = '<';
        $date->t = $time;
        $groupstipcoll[] = $date;
        $groupscond = new stdClass();
        $groupscond->op = '|';
        $groupscond->c = $groupstipcoll;
        $groups = [$groupscond];
        $newavaliability = new stdClass();
        $newavaliability->op = '&';
        $newavaliability->c = $groups;
        $newavaliability->showc = [false];
        return $newavaliability;
    }

}
