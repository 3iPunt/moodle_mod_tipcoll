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
 * The main mod_tipcoll configuration form.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tipcoll_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string(
            'maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        // Adding the FEEDBACK config.
        $mform->addElement('header', 'feedback', get_string('feedback', 'mod_tipcoll'));
        $mform->setExpanded('feedback');

        $mform->addElement('text', 'group_max_length',
            get_string('group_max_length', 'mod_tipcoll'), array('size' => '4'));
        $mform->addHelpButton('group_max_length', 'group_max_length', 'mod_tipcoll');
        $mform->setType('group_max_length', PARAM_INT);
        $mform->setDefault('group_max_length', 5);

        $mform->addElement('date_time_selector', 'feedback_deadline',
            get_string('feedback_deadline', 'mod_tipcoll'));
        $mform->addHelpButton('feedback_deadline', 'feedback_deadline', 'mod_tipcoll');
        $mform->setDefault('feedback_deadline',
            null);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
