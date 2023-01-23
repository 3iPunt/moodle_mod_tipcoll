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
 * Class module
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\factory;

use cm_info;
use coding_exception;
use core_courseformat\external\update_course;
use dml_exception;
use mod_tipcoll\tipcoll;
use moodle_exception;
use MoodleQuickForm;
use phpunit_util;
use stdClass;
use testing_data_generator;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

/**
 * Class module
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class module {

    const MODULES_ACTIVES = ['forum', 'url', 'assign', 'bigbluebuttonbn'];

    /** @var string Mod Name */
    protected $modname = '';

    /** @var testing_data_generator Generator */
    protected $generator;

    /** @var stdClass Course */
    protected $course;

    /** @var int Course ID */
    protected $courseid;

    /** @var int Section */
    protected $section;

    /** @var string Title */
    protected $title;

    /** @var string Intro */
    protected $intro;

    /**
     * constructor.
     *
     * @param string $component
     */
    public function __construct(string $component) {
        $generator = phpunit_util::get_data_generator();
        $this->generator = $generator->get_plugin_generator($component);
    }

    /**
     * Get Title.
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * Get Modname.
     *
     * @return string
     */
    public function get_modname(): string {
        return $this->modname;
    }

    /**
     * Set.
     *
     * @param object $moduleinstance
     * @param int $i
     * @throws dml_exception
     */
    public function set(object $moduleinstance, int $i) {

        $vartitle = 'activity_name_' . $i;
        $title = isset($moduleinstance->$vartitle) ? $moduleinstance->$vartitle : '';
        $varintro = 'activity_intro_' . $i;
        $intro = isset($moduleinstance->$varintro) ? $moduleinstance->$varintro : '';

        $this->courseid = $moduleinstance->course;
        $this->course = get_course($this->courseid);
        $this->title = $title;
        $this->intro = $intro;
    }

    /**
     * Add Instance.
     *
     * @param object $moduleinstance
     * @return bool|int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function add_instance(object $moduleinstance) {
        global $DB;

        $numactivities = (int)get_config('tipcoll', 'numactivities');
        $activitiesdata = [];
        $activities = [];

        $section = $moduleinstance->section;

        // Create Section.
        if (get_config('tipcoll', 'behavior') === 'section') {
            $sectionnew = section::create_section($moduleinstance);
            $section = $sectionnew->section;
        }

        // Create Feedback.
        $factfeedback = new module_feedback();
        $feedinstance = $factfeedback->create_questionnaire(
                $moduleinstance, get_config('feedback', 'pluginname'), '', $section);

        // Create Activities.
        for ($i = 1; $i <= $numactivities; $i++) {
            $modname = get_config('tipcoll', 'activity_type_' . $i);
            $factname = 'mod_tipcoll\factory\module_' . $modname;
            /** @var module $factory */
            $factory = new $factname();
            $activity = $factory->create($moduleinstance, $i, $section);
            $activitiesdata[$i] = $activity;
            $activities[] = $activity['id'];
        }

        // Create TIP Coll.
        $moduleinstance->timecreated = time();
        $moduleinstance->cmids = implode(',', $activities);
        $moduleinstance->feedbackid = $feedinstance->cmid;
        $moduleinstance->cmdata = json_encode($activitiesdata);
        $moduleinstance->showdescription = 1;
        $moduleinstance->section = $section;
        return $DB->insert_record('tipcoll', $moduleinstance);
    }

    /**
     * Update Instance.
     *
     * @param object $moduleinstance
     * @return bool|int
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function update_instance(object $moduleinstance) {
        global $DB;

        $numactivities = (int)get_config('tipcoll', 'numactivities');
        $activitiesdata = [];
        $activities = [];

        for ($i = 1; $i <= $numactivities; $i++) {
            $modname = get_config('tipcoll', 'activity_type_' . $i);
            $factname = 'mod_tipcoll\factory\module_' . $modname;
            /** @var module $factory */
            $factory = new $factname();
            $activity = $factory->update($moduleinstance, $moduleinstance->coursemodule, $i);
            $activitiesdata[$i] = $activity;
            $activities[] = $activity['id'];
        }

        $tipcoll = new tipcoll($moduleinstance->coursemodule);

        if (isset($activities[0])) {
            $cmmoveids = [];
            $cmmoveids[] = $moduleinstance->coursemodule;

            $coursesection = $DB->get_record('course_sections',
                [
                    'course' => $moduleinstance->course,
                    'section' => $moduleinstance->section
                ], 'id', MUST_EXIST);

            $feedback = $tipcoll->get_feedback();

            update_course::execute(
                'cm_move', $moduleinstance->course, $cmmoveids, $coursesection->id, $feedback->get_cm()->id);
        }

        $moduleinstance->timemodified = time();
        $moduleinstance->id = $moduleinstance->instance;
        $moduleinstance->cmids = implode(',', $activities);
        $moduleinstance->cmdata = json_encode($activitiesdata);
        $DB->update_record('tipcoll', $moduleinstance);
        $tipcoll = new tipcoll($moduleinstance->coursemodule);
        $tipcoll->set_restriction_section();
        $tipcoll->set_restriction_activities();
        return true;
    }


    /**
     * Create.
     *
     * @param object $moduleinstance
     * @param int $i
     * @param int $section
     * @return array
     * @throws coding_exception
     * @throws dml_exception|moodle_exception
     */
    public function create(object $moduleinstance, int $i, int $section): array {
        self::set($moduleinstance, $i);
        $record = [
            'course' => $this->course,
            'name' => $this->title,
            'intro' => !empty($this->intro) ? $this->intro : ' ',
            'showdescription' => !empty($this->intro) ? 1 : 0,
            'introformat' => FORMAT_HTML,
            'files' => file_get_unused_draft_itemid(),
        ];
        $options = [
            'section' => $section,
            'visible' => true,
            'showdescription' => 0,
            'availability' => json_encode(tipcoll::get_availability_default_cm($moduleinstance->feedback_deadline))
        ];
        $instance = $this->generator->create_instance($record, $options);

        $activity = [];
        $activity['id'] = $instance->cmid;
        $activity['type'] = $this->modname;
        $activity['name'] = $this->title;
        $activity['intro'] = $this->intro;
        return $activity;
    }

    /**
     * Update.
     *
     * @param object $moduleinstance
     * @param int $cmid
     * @param int $i
     * @return array
     * @throws moodle_exception
     */
    public function update(object $moduleinstance, int $cmid, int $i): array {
        global $DB;
        self::set($moduleinstance, $i);

        $tipcoll = new tipcoll($cmid);
        $instance = $tipcoll->get_activity($i);

        $instance->name = $this->title;
        $instance->intro = !empty($this->intro) ? $this->intro : ' ';

        $DB->update_record($this->modname, $instance);

        $activity = [];
        $activity['id'] = $instance->cmid;
        $activity['type'] = $this->modname;
        $activity['name'] = $this->title;
        $activity['intro'] = $this->intro;
        return $activity;
    }

    /**
     * Add mForm.
     *
     * @param MoodleQuickForm $mform
     * @param int $i
     * @param stdClass|null $cm
     * @throws dml_exception
     */
    public static function add_mform(MoodleQuickForm &$mform, int $i, stdClass $cm = null) {
        $modname = get_config('tipcoll', 'activity_type_' . $i);
        $factname = 'mod_tipcoll\factory\module_' . $modname;
        /** @var module $factory */
        $factory = new $factname();
        $factory->add_mform_item($mform, $i, $cm);
    }

    /**
     * Add mForm Item.
     *
     * @param MoodleQuickForm $mform
     * @param int $i
     * @param stdClass|null $cm
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function add_mform_item(MoodleQuickForm &$mform, int $i, stdClass $cm = null) {
        if (!is_null($cm)) {
            $tipcoll = new tipcoll($cm->id);
            $instance = $tipcoll->get_activity($i);
        } else {
            $instance = null;
        }
        // Name.
        $activityname = 'activity_name_' . $i;
        $mform->addElement('text', $activityname,
            $this->modnamestr . ' - ' . get_string('name'), array('size' => '64'));
        $mform->addRule($activityname, null, 'required', null, 'client');
        $mform->addRule($activityname, get_string(
            'maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType($activityname, PARAM_RAW);
        if (isset($instance)) {
            $mform->setDefault($activityname, $instance->name);
        }
    }

}
