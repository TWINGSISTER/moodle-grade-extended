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

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once 'grade_export_extended.php';

$id = required_param('id', PARAM_INT); // course id

$PAGE->set_url('/grade/export/extended/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_login($course); //$course->fullname for the string of the name
$context = context_course::instance($id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/extended:view', $context);

print_grade_page_head($COURSE->id, 'export', 'extended', get_string('exportto', 'grades') . ' ' . get_string('pluginname', 'gradeexport_extended'),
false, false, true, null, null, null, $actionbar);

export_verify_grades($COURSE->id);

if (!empty($CFG->gradepublishing)) {
    $CFG->gradepublishing = has_capability('gradeexport/extended:publish', $context);
}

$actionurl = new moodle_url('/grade/export/extended/export.php');
// The option 'idnumberrequired' excludes grade items that dont have an ID to use during import.
$formoptions = array(
    'includeseparator'=>true,
    'publishing' => true,
    'simpleui' => true,
    'multipledisplaytypes' => false
);

$mform = new grade_export_form($actionurl, $formoptions);

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used.
$currentgroup = groups_get_course_group($course, true);
if (($groupmode == SEPARATEGROUPS) &&
    (!$currentgroup) &&
    (!has_capability('moodle/site:accessallgroups', $context))) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    die;
}

groups_print_course_menu($course, 'index.php?id='.$id);
echo '<div class="clearer"></div>';
global $CFG;
$urlclean = new moodle_url($CFG->wwwroot .'/grade/export/extended/ggb_dumper.php',
           array('fname' => "CLEAN",'id' => $id,'count'=> 0) );
echo '<a href="'.$urlclean.'"><button>Clean Dumps</button></a>';
$mform->display();

echo $OUTPUT->footer();

