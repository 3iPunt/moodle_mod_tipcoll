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
 * courseview_component
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\output;

use cm_info;
use coding_exception;
use dml_exception;
use mod_tipcoll\tipcoll;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * courseview_component renderable class.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courseview_component implements renderable, templatable {

    /** @var int Course Module ID */
    protected $cmid;

    /** @var stdClass Course Module */
    protected $cm;

    /** @var stdClass[] Instance */
    protected $cmdata;

    /** @var stdClass Instance */
    protected $instance;

    /**
     * view_page constructor.
     *
     * @param int $cmid
     * @throws moodle_exception
     */
    public function __construct(int $cmid) {
        global $DB;
        $this->cmid = $cmid;
        $this->cm = $DB->get_record('course_modules', array( 'id' => $cmid ));
        $this->instance = $DB->get_record('tipcoll', array( 'id' => $this->cm->instance ));
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->title = get_string('welcome', 'mod_tipcoll');
        $data->description = $this->get_description();
        $data->deadline = $this->get_deadline();
        $data->cmid = $this->cmid;
        return $data;
    }

    /**
     * Get Description.
     *
     */
    public function get_description(): string {
        return $this->instance->intro;
    }

    /**
     * Get Deadline.
     *
     * @throws coding_exception
     */
    public function get_deadline(): string {
        return userdate(
            $this->instance->feedback_deadline,
            get_string('strftimedate', 'core_langconfig')
        );
    }

}
