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
 * This file contains the Module List block.
 *
 * @package    block_module_list
 * @copyright  Mayank Tyagi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_module_list extends block_list {

    public function init() {
        $this->title = get_string('pluginname', 'block_module_list');
    }

    public function get_content() {
        global $CFG, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course = $this->page->course;
        $user = $USER->id;
        $completion = new completion_info($course);
        $activities = $completion->get_activities();
        $results = array();
        foreach ($activities as $activity) {

            // Check if current user has visibility on this activity.
            if (!$activity->uservisible) {
                continue;
            }
            // ... @param object $course course object
            // ... @param object|cm_info $cm course module info
            // ... @param int $userid user id
            $exporter = new \core_completion\external\completion_info_exporter(
                $course,
                $activity,
                $user,
            );

            $renderer = $this->page->get_renderer('core');
            $data = (array)$exporter->export($renderer);
            $url = new moodle_url($activity->url, array('forceview' => 1));
            $modname = $activity->get_formatted_name();
            $results[] = array_merge([
                'cmid' => $activity->id,
                'modname' => $modname,
                'completion' => $activity->completion,
                'url' => $url->out(false),
                'added' => $activity->added,
                'timecompleted' => $activity->timecompleted,
            ], $data);
        }

        $activitystatus = '';
        foreach ($results as $value) {
            if ($value['timecompleted'] == 0) {
                $activitystatus = '';
            } else {
                $activitystatus = get_string('status', 'block_module_list');
            }
            // ... making URL for module list
            $this->content->items[] = html_writer::tag('a', $value['cmid'].' '.$value['modname'].' '.date('d-M-Y', $value['added']
            ).' '.$activitystatus, array('href' => $value['url']));

        }

        return $this->content;
    }

    /**
     * Applying Format where this block can be set to view
     */
    public function applicable_formats() {
        return array(
            'site-index' => false, 'course-view' => true, 'mod' => false, 'my' => false, 'admin' => false,
                     'tag' => false);
    }
}


