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

use coding_exception;
use dml_exception;
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

    const MODULES_ACTIVES = ['forum', 'url'];

    /** @var string Mod Name */
    protected $modname = '';

    /** @var testing_data_generator Generator */
    protected $generator;

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
     * @param string $title
     * @param string $intro
     */
    public function __construct(int $section, string $component, string $title, string $intro = '') {
        $generator = phpunit_util::get_data_generator();
        $this->generator = $generator->get_plugin_generator($component);
        $this->section = $section;
        $this->title = $title;
        $this->intro = $intro;
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
     * Create.
     *
     * @param int $courseid
     * @return stdClass
     */
    abstract public function create(int $courseid): \stdClass;

}
