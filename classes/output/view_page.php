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
 * view_page
 *
 * @package     mod_tipcoll
 * @copyright   2023 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\output;

use cm_info;
use coding_exception;
use dml_exception;
use mod_tipcoll\tipcoll;
use mod_tipcoll\tipcoll_user;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * view_page renderable class.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {

    /** @var int Course Module ID */
    protected $cmid;

    /** @var cm_info Course Module */
    protected $cm;

    /**
     * view_page constructor.
     *
     * @param cm_info $cm
     */
    public function __construct(cm_info $cm) {
        $this->cmid = $cm->id;
        $this->cm = $cm;
    }

    /**
     * Export for Template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws dml_exception|moodle_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $USER;

        $tipcoll = new tipcoll($this->cmid);
        $tipcolluser = new tipcoll_user($tipcoll, $USER);

        $groups = $tipcoll->get_groups();
        $participants = $tipcoll->get_feedback()->get_participants();

        $data = new stdClass();
        $data->cmid = $this->cmid;
        $data->is_teacher = $tipcolluser->is_teacher();
        $data->questions = $tipcoll->get_feedback()->get_questions();
        $data->numparticipants = count($participants);
        $data->numgroups = count($groups);
        $data->participants = $participants;
        $data->groups = $groups;
        return $data;
    }

}
