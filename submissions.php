<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/plagiarismlib.php');

$id   = optional_param('id', 0, PARAM_INT);          // Course module ID
$a    = optional_param('a', 0, PARAM_INT);           // voiceshadow ID
$mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?
$download = optional_param('download' , 'none', PARAM_ALPHA); //ZIP download asked for?

$url = new moodle_url('/mod/voiceshadow/submissions.php');
if ($id) {
    if (! $cm = get_coursemodule_from_id('voiceshadow', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $voiceshadow = $DB->get_record("voiceshadow", array("id"=>$cm->instance))) {
        print_error('invalidid', 'voiceshadow');
    }

    if (! $course = $DB->get_record("course", array("id"=>$voiceshadow->course))) {
        print_error('coursemisconf', 'voiceshadow');
    }
    $url->param('id', $id);
} else {
    if (!$voiceshadow = $DB->get_record("voiceshadow", array("id"=>$a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$voiceshadow->course))) {
        print_error('coursemisconf', 'voiceshadow');
    }
    if (! $cm = get_coursemodule_from_instance("voiceshadow", $voiceshadow->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $url->param('a', $a);
}

if ($mode !== 'all') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);
require_login($course->id, false, $cm);

/*
* If is student
*/

if (!has_capability('mod/voiceshadow:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
  $url = new moodle_url('/mod/voiceshadow/viewrubric.php', array("id"=>$id));
  header('Location: '.$url);
  die();
}



$PAGE->requires->js('/mod/voiceshadow/voiceshadow.js');

/// Load up the required voiceshadow code
require($CFG->dirroot.'/mod/voiceshadow/type/'.$voiceshadow->voiceshadowtype.'/voiceshadow.class.php');
$voiceshadowclass = 'voiceshadow_'.$voiceshadow->voiceshadowtype;
$voiceshadowinstance = new $voiceshadowclass($cm->id, $voiceshadow, $cm, $course);

if($download == "zip") {
    $voiceshadowinstance->download_submissions();
} else {
    $voiceshadowinstance->submissions($mode);   // Display or process the submissions
}