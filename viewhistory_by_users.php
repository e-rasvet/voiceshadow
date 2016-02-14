<?php  // $Id: viewhistory.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $


require_once '../../config.php';
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once 'lib.php';
require_once ($CFG->libdir.'/gradelib.php');


$id                     = optional_param('id', 0, PARAM_INT); 
$a                      = optional_param('a', 'list', PARAM_TEXT);  
$sort                   = optional_param('sort', 'username', PARAM_CLEAN); 
$orderby                = optional_param('orderby', 'ASC', PARAM_CLEAN); 


if ($id) {
    if (! $cm = get_coursemodule_from_id('voiceshadow', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        error('Course is misconfigured');
    }

    if (! $voiceshadow = $DB->get_record('voiceshadow', array('id' => $cm->instance))) {
        error('Course module is incorrect');
    }
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context       = get_context_instance(CONTEXT_MODULE, $cm->id);
$contextcourse = get_context_instance(CONTEXT_COURSE, $course->id);

if (voiceshadow_is_ios() && is_dir($CFG->dirroot.'/theme/mymobile')) {} else
  $PAGE->requires->js('/mod/voiceshadow/js/jquery.min.js', true);
  
$PAGE->requires->js('/mod/voiceshadow/js/flowplayer.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/swfobject.js', true);


/// Print the page header
$strvoiceshadows = get_string('modulenameplural', 'voiceshadow');
$strvoiceshadow  = get_string('modulename', 'voiceshadow');

$PAGE->set_url('/mod/voiceshadow/viewhistory.php', array('id' => $id));
    
$title = $course->shortname . ': ' . format_string(get_string('modulename', 'voiceshadow'));
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

require_once ('tabs.php');

$coursestudents = get_enrolled_users($contextcourse);

$studentssort = array();

foreach($coursestudents as $key => $coursestudent){
  $studentssort[$key] = fullname($coursestudent);
}

asort($studentssort);

if ($orderby == "DESC" && $sort == "username")
  $studentssort = array_reverse($studentssort, true);


$table = new html_table();

$titlesarray = array (get_string("cell1::student", "voiceshadow")=>'username');
//$table->head  = array(get_string("cell1::student", "voiceshadow"));
$table->head  = voiceshadow_make_table_headers ($titlesarray, $orderby, $sort, '?id='.$id.'&a='.$a);
$table->align = array ("left");
$table->width = "100%";

foreach($studentssort as $stid => $stname) {
  $cell = new html_table_cell(html_writer::link(new moodle_url('/mod/voiceshadow/viewhistory.php', array("id" => $id, "ids" => $coursestudents[$stid]->id, 'a'=>'history')), fullname($coursestudents[$stid])));
  $cells = array($cell);
  $row = new html_table_row($cells);
  $table->data[] = $row;
}

echo html_writer::table($table);


/// Finish the page
echo $OUTPUT->footer();



