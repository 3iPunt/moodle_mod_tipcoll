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
 * Class module_feedback
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\factory;

use coding_exception;
use dml_exception;
use mod_feedback_generator;
use mod_tipcoll\tipcoll;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

/**
 * Class module_feedback
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_feedback extends module {

    /** @var string Mod Name */
    protected $modname = 'feedback';

    /** @var string Mod Name String */
    protected $modnamestr;

    /** @var mod_feedback_generator Generator */
    protected $generator;

    /**
     * constructor.
     *
     * @throws coding_exception
     */
    public function __construct() {
        parent::__construct('mod_feedback');
        $this->modnamestr = get_string('pluginname', 'feedback');
    }

    /**
     * Create questionnaire.
     *
     * @param object $moduleinstance
     * @param string $title
     * @param string $intro
     * @param int $section
     * @return stdClass
     * @throws moodle_exception
     */
    public function create_questionnaire(object $moduleinstance, string $title, string $intro, int $section): stdClass {
        $record = [
            'course' => $moduleinstance->course,
            'name' => $title,
            'intro' => !empty($intro) ? $intro : ' ',
            'showdescription' => !empty($intro) ? 1 : 0,
            'introformat' => FORMAT_HTML,
            'anonymous' => 2,
            'files' => file_get_unused_draft_itemid(),
        ];
        $options = [
            'section' => $section,
            'visible' => false,
            'showdescription' => 0
        ];
        $res = $this->generator->create_instance($record, $options);

        // TODO. create questions and responses.

        return $res;
    }

}
