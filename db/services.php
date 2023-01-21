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
 * @copyright   3iPunt <https://www.tresipunt.com/>
 */

use mod_tipcoll\external\feedback_external;
use mod_tipcoll\external\tipcoll_external;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_tipcoll_feedback' => [
        'classname' => feedback_external::class,
        'methodname' => 'feedback',
        'description' => 'Feedback > Get Feedback with questions and responses',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    ],
    'mod_tipcoll_get_content' => [
        'classname' => tipcoll_external::class,
        'methodname' => 'content',
        'description' => 'TIP Coll > Get MODULE with status, groups or questions and responses',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    ],
];
$services = [
    'mod_tipcoll' => [
        'functions' => [
            'mod_tipcoll_feedback',
            'mod_tipcoll_get_content',
        ],
        'restrictedusers' => 0,
        'enabled' => 1
    ]
];