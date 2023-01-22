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

use coding_exception;
use context_course;
use core_user;
use dml_exception;
use mod_tipcoll\models\feedback_user;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;
global $CFG;

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
     * Get User.
     *
     * @return stdClass
     */
    public function get_user(): stdClass {
        return $this->user;
    }

    /**
     * Get Group.
     *
     * @return stdClass
     */
    public function get_group(): ?stdClass {
        return $this->group;
    }

    /**
     * Get Status.
     *
     * @return string
     * @throws moodle_exception
     */
    public function get_status(): string {
        if ($this->tipcoll->get_deadline_timestamp() < time()) {
            return 'deadline';
        } else {
            $feedbackuser = new feedback_user($this->tipcoll->get_feedback(), $this);
            if ($feedbackuser->is_completed()) {
                return 'completed';
            } else {
                return 'feedback';
            }
        }
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
            if ($idnumber[0] === 'tipcoll') {
                $groupcmid = (int)$idnumber[1];
                if ($groupcmid === $this->tipcoll->get_cmid()) {
                    $this->group = $group;
                    break;
                }
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
     * @throws coding_exception
     */
    public function get_members(): array {
        global $PAGE;
        $items = [];
        $members = groups_get_groups_members([$this->group->id]);
        foreach ($members as $member) {
            $userpicture = new \user_picture($member);
            $userpicture->size = 1;
            $item = [];
            $item['picture'] = $userpicture->get_url($PAGE)->out(false);
            $item['name'] = fullname($member);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Is Teacher?
     *
     * @return bool
     * @throws coding_exception
     */
    public function is_teacher(): bool {
        return has_capability('moodle/course:update', context_course::instance($this->tipcoll->get_course()->id));
    }

    /**
     * Is Student?
     *
     * @return bool
     */
    public function is_student(): bool {
        $userroles = get_users_roles(context_course::instance($this->tipcoll->get_course()->id), [$this->user->id]);
        foreach ($userroles as $role) {
            $role = current($role);
            if ($role->shortname === 'student') {
                return true;
            }
        }
        return false;
    }

    /**
     * Assign user to Group.
     *
     * @param int $groupid
     * @return bool
     * @throws coding_exception
     */
    public function assign_group(int $groupid): bool {
        $res = true;
        if (!empty($this->group)) {
            $res = groups_remove_member($this->group->id, $this->user->id);
        }
        if ($groupid !== 0) {
            $res = groups_add_member($groupid, $this->user->id);
        }
        return $res;

    }

    /**
     * Get Groups by user.
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_groups(): array {
        $items = [];
        foreach ($this->tipcoll->get_groups() as $group) {
            $item = new stdClass();
            $item->id = $group->id;
            $item->name = $group->name;
            $item->selected = ($group->id === $this->group->id);
            $items[] = $item;
        }
        return $items;
    }

}
