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
 * Plugin administration pages are defined here.
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'tipcoll/general',
        get_string('generalheading', 'mod_tipcoll'),
        get_string('generalheadingdesc', 'mod_tipcoll')));

    $choicesbehavior = [
        //'label' => get_string('label', 'mod_tipcoll'),
        'section' => get_string('section', 'mod_tipcoll')
    ];

    $behavior = new admin_setting_configselect(
        'tipcoll/behavior',
        new lang_string('behavior', 'mod_tipcoll'),
        new lang_string('behavior_help', 'mod_tipcoll'),
        'section', $choicesbehavior
    );

    $settings->add($behavior);

    $numactivities = new admin_setting_configtext(
        'tipcoll/numactivities',
        new lang_string('numactivities', 'mod_tipcoll'),
        new lang_string('numactivities_help', 'mod_tipcoll'),
        4, PARAM_INT
    );

    $settings->add($numactivities);

    $settings->add(new admin_setting_heading(
        'tipcoll/activities',
        get_string('activitiesheading', 'mod_tipcoll'),
        get_string('activitiesheading_help', 'mod_tipcoll')));

    global $DB, $CFG;
    $choices = ['not' => get_string('notselected', 'mod_tipcoll')];
    foreach (\mod_tipcoll\factory\module::MODULES_ACTIVES as $item) {
        // Exclude modules if the code doesn't exist.
        if (file_exists("$CFG->dirroot/mod/$item/lib.php")) {
            $choices[$item] = $item;
        }
    }

    $max = 10;
    $defaults = ['not', 'forum', 'url', 'url', 'url',
        'not', 'not', 'not', 'not', 'not', 'not', 'not'];
    for ($i = 1; $i <= $max; $i++) {
        $settings->add(new admin_setting_configselect(
            'tipcoll/activity_type_' . $i,
            new lang_string('activity_type', 'mod_tipcoll') . ' ' . $i,
            '', $defaults[$i], $choices
        ));
    }

    $settings->add($numactivities);

    $settings->add(new admin_setting_heading(
            'tipcoll/feedback',
            get_string('feedback', 'mod_tipcoll'),
            get_string('feedback_help', 'mod_tipcoll')));

    $feedbackq1 = new admin_setting_configtext(
            'tipcoll/feedbackq1',
            new lang_string('feedbackq1', 'mod_tipcoll'),
            '',
            get_string('feedbackq1_q', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackq1);

    $feedbackr1 = new admin_setting_configtext(
            'tipcoll/feedbackr1',
            new lang_string('feedbackr1', 'mod_tipcoll'),
            new lang_string('feedbackr_help', 'mod_tipcoll'),
            get_string('feedbackq1_r', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackr1);

    $feedbackq2 = new admin_setting_configtext(
            'tipcoll/feedbackq2',
            new lang_string('feedbackq2', 'mod_tipcoll'),
            '',
            get_string('feedbackq2_q', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackq2);

    $feedbackr2 = new admin_setting_configtext(
            'tipcoll/feedbackr2',
            new lang_string('feedbackr2', 'mod_tipcoll'),
            new lang_string('feedbackr_help', 'mod_tipcoll'),
            get_string('feedbackq2_r', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackr2);

    $feedbackq3 = new admin_setting_configtext(
            'tipcoll/feedbackq3',
            new lang_string('feedbackq3', 'mod_tipcoll'),
            '',
            get_string('feedbackq3_q', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackq3);

    $feedbackr3 = new admin_setting_configtext(
            'tipcoll/feedbackr3',
            new lang_string('feedbackr3', 'mod_tipcoll'),
            new lang_string('feedbackr_help', 'mod_tipcoll'),
            get_string('feedbackq3_r', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackr3);

    $feedbackq4 = new admin_setting_configtext(
            'tipcoll/feedbackq4',
            new lang_string('feedbackq4', 'mod_tipcoll'),
            '',
            get_string('feedbackq4_q', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackq4);

    $feedbackr4 = new admin_setting_configtext(
            'tipcoll/feedbackr4',
            new lang_string('feedbackr4', 'mod_tipcoll'),
            new lang_string('feedbackr_help', 'mod_tipcoll'),
            get_string('feedbackq4_r', 'mod_tipcoll'), PARAM_TEXT
    );
    $settings->add($feedbackr4);

}
