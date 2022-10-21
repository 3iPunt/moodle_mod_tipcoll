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
        $this->section = $moduleinstance->section;
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

        for ($i = 1; $i <= $numactivities; $i++) {
            $modname = get_config('tipcoll', 'activity_type_' . $i);
            $factname = 'mod_tipcoll\factory\module_' . $modname;
            /** @var module $factory */
            $factory = new $factname();
            $activity = $factory->create($i, $moduleinstance);
            $activitiesdata[$i] = $activity;
            $activities[] = $activity['id'];
        }

        $moduleinstance->timecreated = time();
        $moduleinstance->cmids = implode(',', $activities);
        $moduleinstance->cmdata = json_encode($activitiesdata);
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
            $activity = $factory->update($i, $moduleinstance, $moduleinstance->coursemodule);
            $activitiesdata[$i] = $activity;
            $activities[] = $activity['id'];
        }

        if (isset($activities[0])) {
            $cmmoveids = [];
            $cmmoveids[] = $moduleinstance->coursemodule;
            update_course::execute(
                'cm_move', $moduleinstance->course, $cmmoveids, $moduleinstance->section, $activities[0]);
        }

        $moduleinstance->timemodified = time();
        $moduleinstance->id = $moduleinstance->instance;
        $moduleinstance->cmids = implode(',', $activities);
        $moduleinstance->cmdata = json_encode($activitiesdata);
        $DB->update_record('tipcoll', $moduleinstance);
        return true;
    }

    /**
     * Create.
     *
     * @param int $i
     * @param object $moduleinstance
     * @return array
     */
    abstract public function create(int $i, object $moduleinstance): array;

    /**
     * Update.
     *
     * @param int $i
     * @param object $moduleinstance
     * @param int $cmid
     * @return array
     */
    abstract public function update(int $i, object $moduleinstance, int $cmid): array;

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
     * Create.
     *
     * @param MoodleQuickForm $mform
     * @param int $i
     * @param stdClass|null $cm
     */
    abstract public function add_mform_item(MoodleQuickForm &$mform, int $i, stdClass $cm = null);

}
