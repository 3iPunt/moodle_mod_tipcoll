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
 * Class module_forum
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\factory;

use dml_exception;
use mod_forum_generator;

/**
 * Class module_forum
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_forum extends module {

    /** @var string Mod Name */
    protected $modname = 'url';

    /** @var mod_forum_generator Generator */
    protected $generator;

    /** @var string Link */
    protected $link;

    /**
     * constructor.
     *
     * @param int $section
     * @param string $title
     * @param string $intro
     */
    public function __construct(int $section, string $title, string $intro = '') {
        parent::__construct($section, 'mod_forum', $title, $intro);
    }

    /**
     * Create.
     *
     * @param int $courseid
     * @return bool
     * @throws dml_exception
     */
    public function create(int $courseid): \stdClass {
        $course = get_course($courseid);
        $record = [
            'course' => $course,
            'name' => $this->title,
            'intro' => !empty($this->intro) ? $this->intro : ' ',
            'showdescription' => !empty($this->intro) ? 1 : 0,
            'introformat' => FORMAT_HTML,
            'files' => file_get_unused_draft_itemid(),
        ];
        $options = [
            'section' => $this->section,
            'visible' => true,
            'showdescription' => !empty($this->intro)
        ];
        return $this->generator->create_instance($record, $options);
    }

}
