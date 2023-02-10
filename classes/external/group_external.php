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
 * @package     mod_tipcoll
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright   3iPunt <https://www.tresipunt.com/>
 */

namespace mod_tipcoll\external;

use context_course;
use context_module;
use core\plugininfo\enrol;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_tipcoll\tipcoll;
use mod_tipcoll\tipcoll_user;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

class group_external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function create_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module ID'),
                'name' => new external_value(PARAM_TEXT, 'Name Group')
            )
        );
    }

    /**
     * create.
     *
     * @param int $cmid
     * @param string $name
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function create(int $cmid, string $name): array {
        self::validate_parameters(
            self::create_parameters(), [
                'cmid' => $cmid,
                'name' => $name
            ]
        );

        $error = '';
        $success = false;

        try {
            $context = context_module::instance($cmid);
            if (has_capability('moodle/course:managegroups', $context)) {
                $tipcoll = new tipcoll($cmid);
                $res = $tipcoll->create_group($name);
                if ($res) {
                    $success = true;
                } else {
                    $error = 'UNKNOWN';
                }
            } else {
                $error = 'USER HAS NOT CAPABILITY (moodle/course:managegroups)';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function create_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
            )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function delete_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module ID'),
                'groupid' => new external_value(PARAM_INT, 'Group ID')
            )
        );
    }

    /**
     * create.
     *
     * @param int $cmid
     * @param int $groupid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function delete(int $cmid, int $groupid): array {
        self::validate_parameters(
            self::delete_parameters(), [
                'cmid' => $cmid,
                'groupid' => $groupid
            ]
        );

        $error = '';
        $success = false;

        try {
            $context = context_module::instance($cmid);
            if (has_capability('moodle/course:managegroups', $context)) {
                $tipcoll = new tipcoll($cmid);
                $res = $tipcoll->delete_group($groupid);
                if ($res) {
                    $success = true;
                } else {
                    $error = 'UNKNOWN';
                }
            } else {
                $error = 'USER HAS NOT CAPABILITY (moodle/course:managegroups)';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function delete_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
            )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function assign_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module ID'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'groupid' => new external_value(PARAM_INT, 'Group ID')
            )
        );
    }

    /**
     * create.
     *
     * @param int $cmid
     * @param int $userid
     * @param int $groupid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function assign(int $cmid, int $userid, int $groupid): array {
        self::validate_parameters(
            self::assign_parameters(), [
                'cmid' => $cmid,
                'userid' => $userid,
                'groupid' => $groupid
            ]
        );

        $error = '';
        $success = false;

        try {
            $context = context_module::instance($cmid);
            if (has_capability('moodle/course:managegroups', $context)) {
                $tipcoll = new tipcoll($cmid);
                $user = \core_user::get_user($userid);
                $tipcolluser = new tipcoll_user($tipcoll, $user);
                $res = $tipcolluser->assign_group($groupid);
                if ($res) {
                    $success = true;
                } else {
                    $error = 'UNKNOWN';
                }
            } else {
                $error = 'USER HAS NOT CAPABILITY (moodle/course:managegroups)';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function assign_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
            )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function distribute_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module ID'),
                'usersid' => new external_value(PARAM_RAW, 'Users ID separated by commas'),
                'numusers' => new external_value(PARAM_INT, 'Number of users in a group')
            )
        );
    }

    /**
     * create.
     *
     * @param int $cmid
     * @param string $usersid
     * @param int $numusers
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function distribute(int $cmid, string $usersid, int $numusers): array {
        self::validate_parameters(
            self::distribute_parameters(), [
                'cmid' => $cmid,
                'usersid' => $usersid,
                'numusers' => $numusers
            ]
        );

        $error = '';
        $success = false;

        try {
            $context = context_module::instance($cmid);
            if (has_capability('moodle/course:managegroups', $context)) {
                $tipcoll = new tipcoll($cmid);
                $usersid = explode(',', $usersid);
                $users = [];
                $uk = 1;
                foreach ($usersid as $userid) {
                    $user = \core_user::get_user($userid);
                    $tipcolluser = new tipcoll_user($tipcoll, $user);
                    if ($tipcolluser->is_student()) {
                        $users[] = $tipcolluser;
                    }
                }
                $totalusers = count($users);
                $totalgroups = ceil($totalusers / $numusers);
                for ($i = 1; $i <= $totalgroups; $i++) {
                    $groupid = $tipcoll->create_group(get_string('group') . ' ' . rand(1, 500));
                    for ($k = 1; $k <= $numusers; $k++) {
                        $keys = array_keys($users);
                        if (isset($users[current($keys)])) {
                            $tipcolluser = $users[current($keys)];
                            $tipcolluser->assign_group($groupid);
                            unset($users[current($keys)]);
                        } else {
                            break;
                        }
                    }
                }
                $success = true;
            } else {
                $error = 'USER HAS NOT CAPABILITY (moodle/course:managegroups)';
            }
        } catch (moodle_exception $e) {
            $error = $e->getMessage();
        }

        return [
            'success' => $success,
            'error' => $error
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function distribute_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
            )
        );
    }

}

