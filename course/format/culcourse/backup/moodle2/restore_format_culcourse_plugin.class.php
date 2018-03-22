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
 * Specialised restore for format_culcourse
 *
 * @package   format_culcourse
 * @category  backup
 * @copyright 2017 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/culcourse/backup/moodle2/restore_format_topics_trait.php');
require_once($CFG->dirroot. '/course/format/culcourse/backup/moodle2/restore_format_weeks_trait.php');

define('FORMATTOPICS', 1);
define('FORMATWEEKS', 2);

/**
 * Specialised restore for format_culcourse
 *
 * Processes 'numsections' from the old backup files and hides sections that used to be "orphaned"
 *
 * @package   format_culcourse
 * @category  backup
 * @copyright 2017 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_format_culcourse_plugin extends restore_format_plugin {

    use restore_format_topics_trait;
    use restore_format_weeks_trait;

    /** @var int */
    // protected $baseclass;

    // /**
    //  * Creates a new instance of class
    //  *
    //  * Please use {@link course_get_format($courseorid)} to get an instance of the format class
    //  *
    //  * @param string $format
    //  * @param int $courseid
    //  * @return format_base
    //  */
    // protected function __construct($plugintype, $pluginname, $step) {        
    //     global $DB;

    //     $baseclasses = [
    //         1 => 'format_topics_',
    //         2 => 'format_weeks_'
    //     ];

    //     // // Get record from db or default.
    //     // $record = $DB->get_record('course_format_options',
    //     //                         array('courseid' => $courseid,
    //     //                               'format' => 'culcourse',
    //     //                               'name' => 'baseclass'
    //     //                             ), 'value');

    //     // // course_get_format($course) @TODO

    //     // if ($record) {
    //     //     $baseclass = $record->value;
    //     // } else {
    //     //     $config = get_config('format_culcourse');
    //     //     $baseclass = $config->defaultbaseclass;
    //     // }

    //     parent::__construct($plugintype, $pluginname, $step);

    //     $this->baseclass = $baseclasses[$baseclass];
    // }    

    /**
     * Creates a dummy path element in order to be able to execute code after restore
     *
     * @return restore_path_element[]
     */
    public function define_course_plugin_structure() {

    }

    /**
     * Dummy process method
     */
    public function process_dummy_course() {

    }

    /**
     * Executed after course restore is complete
     *
     * This method is only executed if course configuration was overridden
     */
    public function after_restore_course() {
        global $DB;







        if ($backupinfo->original_course_format === 'culcourse' && isset($data['tags']['layoutstructure'])) {
            if ($data['tags']['layoutstructure'] !== 1 && $data['tags']['layoutstructure'] !== 4) {
                $DB->execute("UPDATE {course_format_options} SET value = 2 WHERE courseid = ? AND format = 'culcourse' AND name = 'baseclass'",
                        [$this->step->get_task()->get_courseid()]);
            }
        }
    }
}
