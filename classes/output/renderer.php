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
 * Class renderer
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tipcoll\output;

use moodle_exception;
use plugin_renderer_base;

/**
 * Class renderer
 *
 * @package     mod_tipcoll
 * @copyright   2022 Tresipunt - Antonio Manzano <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer to template.
     *
     * @param courseview_component $componet
     * @return bool|string
     * @throws moodle_exception
     */
    public function render_courseview_component(courseview_component $componet) {
        $data = $componet->export_for_template($this);
        return parent::render_from_template('mod_tipcoll/courseview_component', $data);
    }

    /**
     * Defer to template.
     *
     * @param view_page $componet
     * @return bool|string
     * @throws moodle_exception
     */
    public function render_view_page(view_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_tipcoll/view_page', $data);
    }

}
