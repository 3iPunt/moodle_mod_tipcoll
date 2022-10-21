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
 * Class Observer mod_tipcoll
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 */

use core\event\course_module_created;
use mod_tipcoll\tipcoll;

defined('MOODLE_INTERNAL') || die();

global $CFG;


/**
 * Class Event observer for mod_tipcoll_observer.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_tipcoll_observer {

    /**
     * Evento que controla la creación del curso.
     *
     * @param course_module_created $event
     * @return bool
     * @throws moodle_exception
     */
    public static function course_module_created(course_module_created $event): bool {
         $instanceid = isset($event->other['instanceid']) ? $event->other['instanceid'] : null;
        $modulename = isset($event->other['modulename']) ? $event->other['modulename'] : null;
        if ($modulename === 'tipcoll') {
            list($course, $cm) = get_course_and_cm_from_instance($instanceid, 'tipcoll');
            $tipcoll = new tipcoll($cm->id);
            $feedback = $tipcoll->get_feedback();
            /** @var cm_info $cminfo */
            $cminfo = $cm;
            $cmmoveids = [];
            $cmmoveids[] = $cm->id;
            \core_courseformat\external\update_course::execute(
                'cm_move', $course->id, $cmmoveids, $cminfo->section, $feedback->cmid);
        }

        return true;
    }
}
