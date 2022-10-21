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
 * Class section
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
 * Class section
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section {

    /**
     * Create Section.
     *
     * @param object $moduleinstance
     * @return stdClass
     * @throws moodle_exception
     */
    public static function create_section(object $moduleinstance): stdClass {
        $section = course_create_section($moduleinstance->course);
        $data = new stdClass();
        $data->name = $moduleinstance->name;
        $data->summary = $moduleinstance->intro;
        $data->summaryformat = FORMAT_HTML;
        course_update_section($section->course, $section, $data);
        return $section;
    }

}
