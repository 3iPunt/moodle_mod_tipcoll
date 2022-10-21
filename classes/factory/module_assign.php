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
 * Class module_assign
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll\factory;

use coding_exception;
use dml_exception;
use mod_assign_generator;
use mod_tipcoll\tipcoll;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

/**
 * Class module_assign
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_assign extends module {

    /** @var string Mod Name */
    protected $modname = 'assign';

    /** @var string Mod Name String */
    protected $modnamestr;

    /** @var mod_assign_generator Generator */
    protected $generator;

    /**
     * constructor.
     *
     * @throws coding_exception
     */
    public function __construct() {
        parent::__construct('mod_assign');
        $this->modnamestr = get_string('pluginname', 'assign');
    }

}
