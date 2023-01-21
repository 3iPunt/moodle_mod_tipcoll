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
 * Class tipcoll_user
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_tipcoll;

use cm_info;
use coding_exception;
use dml_exception;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

/**
 * Class tipcoll_user
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tipcoll_user {

    /** @var tipcoll Tip Coll */
    protected $tipcoll;

    /** @var stdClass User */
    protected $user;

    /** @var stdClass Group */
    protected $group;

    /**
     * constructor.
     *
     * @param tipcoll $tipcoll
     * @param stdClass $user
     * @throws dml_exception
     */
    public function __construct(tipcoll $tipcoll, stdClass $user) {
        $this->tipcoll = $tipcoll;
        $this->user = $user;
        $this->set_group();
    }

    /**
     * Set Group.
     *
     * @throws dml_exception
     */
    protected function set_group() {
        $groups = groups_get_user_groups($this->tipcoll->get_course()->id, $this->user->id);
        $groups = $groups[0];
        foreach ($groups as $gr) {
            $group = groups_get_group($gr);
            $idnumber = explode('_', $group->idnumber);
            $groupcmid = (int)$idnumber[1];
            if ($groupcmid === $this->tipcoll->get_cmid()) {
                $this->group = $group;
                break;
            }
        }
    }

    /**
     * Has group?
     *
     * @return bool
     */
    public function has_group(): bool {
        return !is_null($this->group);
    }

    /**
     * Get Groupname
     *
     * @return string
     */
    public function get_groupname(): string {
        return isset($this->group) ? $this->group->name : '';
    }

    /**
     * Get Members.
     *
     * @return stdClass[]
     */
    public function get_members(): array {
        global $PAGE;
        $items = [];
        $members = groups_get_groups_members([$this->group->id]);
        foreach ($members as $member) {
            $userpicture = new \user_picture($this->user);
            $userpicture->size = 1;
            $item = [];
            $item['picture'] = $userpicture->get_url($PAGE)->out(false);
            $item['name'] = fullname($member);
            $items[] = $item;
        }
        return $items;
    }

}
