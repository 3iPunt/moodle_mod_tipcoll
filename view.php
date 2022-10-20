<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_tipcoll.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_tipcoll\event\course_module_viewed;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $DB, $PAGE, $OUTPUT;

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$t = optional_param('t', 0, PARAM_INT);
$u = optional_param('u', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id(
        'tipcoll', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record(
        'course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record(
        'tipcoll', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $moduleinstance = $DB->get_record(
        'tipcoll', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record(
        'course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance(
        'tipcoll', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    debugging(get_string('missingidandcmid', 'mod_tipcoll'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('tipcoll', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/tipcoll/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->css('/mod/tipcoll/styles.css');

echo $OUTPUT->header();
$output = $PAGE->get_renderer('mod_tipcoll');


echo $OUTPUT->footer();

