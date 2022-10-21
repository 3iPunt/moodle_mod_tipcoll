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

use coding_exception;
use dml_exception;
use mod_forum_generator;
use mod_tipcoll\tipcoll;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

/**
 * Class module_forum
 *
 * @package     mod_tipcoll
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_forum extends module {

    /** @var string Mod Name */
    protected $modname = 'forum';

    /** @var string Mod Name String */
    protected $modnamestr;

    /** @var mod_forum_generator Generator */
    protected $generator;

    /** @var string Link */
    protected $link;

    /**
     * constructor.
     *
     * @throws coding_exception
     */
    public function __construct() {
        parent::__construct('mod_forum');
        $this->modnamestr = get_string('pluginname', 'forum');
    }

    /**
     * Create.
     *
     * @param int $i
     * @param $moduleinstance
     * @return array
     * @throws dml_exception
     */
    public function create(int $i, $moduleinstance): array {
        parent::set($moduleinstance, $i);
        $record = [
            'course' => $this->course,
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
        $instance = $this->generator->create_instance($record, $options);

        $activity = [];
        $activity['id'] = $instance->cmid;
        $activity['type'] = $this->modname;
        $activity['name'] = $this->title;
        $activity['intro'] = $this->intro;
        return $activity;
    }

    /**
     * Update.
     *
     * @param int $i
     * @param object $moduleinstance
     * @param int $cmid
     * @return array
     * @throws moodle_exception
     */
    public function update(int $i, object $moduleinstance, int $cmid): array {
        global $DB;
        parent::set($moduleinstance, $i);

        $tipcoll = new tipcoll($cmid);
        $instance = $tipcoll->get_activity($i);

        $instance->name = $this->title;
        $instance->intro = !empty($this->intro) ? $this->intro : ' ';

        $DB->update_record($this->modname, $instance);

        $activity = [];
        $activity['id'] = $instance->cmid;
        $activity['type'] = $this->modname;
        $activity['name'] = $this->title;
        $activity['intro'] = $this->intro;
        return $activity;
    }

    /**
     * Add mForm Item.
     *
     * @param MoodleQuickForm $mform
     * @param int $i
     * @param stdClass|null $cm
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function add_mform_item(MoodleQuickForm &$mform, int $i, stdClass $cm = null) {
        if (!is_null($cm)) {
            $tipcoll = new tipcoll($cm->id);
            $instance = $tipcoll->get_activity($i);
        } else {
            $instance = null;
        }
        // Name.
        $activityname = 'activity_name_' . $i;
        $mform->addElement('text', $activityname,
            $this->modnamestr . ' - ' . get_string('name'), array('size' => '64'));
        $mform->addRule($activityname, null, 'required', null, 'client');
        $mform->addRule($activityname, get_string(
            'maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType($activityname, PARAM_RAW);
        if (isset($instance)) {
            $mform->setDefault($activityname, $instance->name);
        }
    }

}
