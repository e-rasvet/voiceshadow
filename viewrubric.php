<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');

$id = optional_param('id', 0, PARAM_INT);  // Course Module ID
$a  = optional_param('a', 0, PARAM_INT);   // voiceshadow ID

$url = new moodle_url('/mod/voiceshadow/view.php');
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
        print_error('invalidid', 'voiceshadow');
    }
    if (! $course = $DB->get_record("course", array("id"=>$voiceshadow->course))) {
        print_error('coursemisconf', 'voiceshadow');
    }
    if (! $cm = get_coursemodule_from_instance("voiceshadow", $voiceshadow->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $url->param('a', $a);
}

$PAGE->set_url($url);
require_login($course, true, $cm);

$PAGE->requires->js('/mod/voiceshadow/voiceshadow.js');

require ("$CFG->dirroot/mod/voiceshadow/type/$voiceshadow->voiceshadowtype/voiceshadow.class.php");
$voiceshadowclass = "voiceshadow_$voiceshadow->voiceshadowtype";
$voiceshadowinstance = new $voiceshadowclass($cm->id, $voiceshadow, $cm, $course);

/// Mark as viewed
$completion=new completion_info($course);
$completion->set_module_viewed($cm);

$voiceshadowinstance->view();   // Actually display the voiceshadow!