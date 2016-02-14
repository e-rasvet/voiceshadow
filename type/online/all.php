<?php

//===================================================
// all.php
//
// Displays a complete list of online voiceshadows
// for the course. Rather like what happened in
// the old Journal activity.
// Howard Miller 2008
// See MDL-14045
//===================================================

require_once("../../../../config.php");
require_once("{$CFG->dirroot}/mod/voiceshadow/lib.php");
require_once($CFG->libdir.'/gradelib.php');
require_once('voiceshadow.class.php');

// get parameter
$id = required_param('id', PARAM_INT);   // course

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourse');
}

$PAGE->set_url('/mod/voiceshadow/type/online/all.php', array('id'=>$id));

require_course_login($course);

// check for view capability at course level
$context = get_context_instance(CONTEXT_COURSE,$course->id);
require_capability('mod/voiceshadow:view',$context);

// various strings
$str = new stdClass;
$str->voiceshadows = get_string("modulenameplural", "voiceshadow");
$str->duedate = get_string('duedate','voiceshadow');
$str->duedateno = get_string('duedateno','voiceshadow');
$str->editmysubmission = get_string('editmysubmission','voiceshadow');
$str->emptysubmission = get_string('emptysubmission','voiceshadow');
$str->novoiceshadows = get_string('novoiceshadows','voiceshadow');
$str->onlinetext = get_string('typeonline','voiceshadow');
$str->submitted = get_string('submitted','voiceshadow');

$PAGE->navbar->add($str->voiceshadows, new moodle_url('/mod/voiceshadow/index.php', array('id'=>$id)));
$PAGE->navbar->add($str->onlinetext);

// get all the voiceshadows in the course
$voiceshadows = get_all_instances_in_course('voiceshadow',$course, $USER->id );

$sections = get_all_sections($course->id);

// array to hold display data
$views = array();

// loop over voiceshadows finding online ones
foreach( $voiceshadows as $voiceshadow ) {
    // only interested in online voiceshadows
    if ($voiceshadow->voiceshadowtype != 'online') {
        continue;
    }

    // check we are allowed to view this
    $context = get_context_instance(CONTEXT_MODULE, $voiceshadow->coursemodule);
    if (!has_capability('mod/voiceshadow:view',$context)) {
        continue;
    }

    // create instance of voiceshadow class to get
    // submitted voiceshadows
    $onlineinstance = new voiceshadow_online( $voiceshadow->coursemodule );
    $submitted = $onlineinstance->submittedlink(true);
    $submission = $onlineinstance->get_submission();

    // submission (if there is one)
    if (empty($submission)) {
        $submissiontext = $str->emptysubmission;
        if (!empty($voiceshadow->timedue)) {
            $submissiondate = "{$str->duedate} ".userdate( $voiceshadow->timedue );

        } else {
            $submissiondate = $str->duedateno;
        }

    } else {
        $submissiontext = format_text( $submission->data1, $submission->data2 );
        $submissiondate  = "{$str->submitted} ".userdate( $submission->timemodified );
    }

    // edit link
    $editlink = "<a href=\"{$CFG->wwwroot}/mod/voiceshadow/view.php?".
        "id={$voiceshadow->coursemodule}&amp;edit=1\">{$str->editmysubmission}</a>";

    // format options for description
    $formatoptions = new stdClass;
    $formatoptions->noclean = true;

    // object to hold display data for voiceshadow
    $view = new stdClass;

    // start to build view object
    $view->section = get_section_name($course, $sections[$voiceshadow->section]);

    $view->name = $voiceshadow->name;
    $view->submitted = $submitted;
    $view->description = format_module_intro('voiceshadow', $voiceshadow, $voiceshadow->coursemodule);
    $view->editlink = $editlink;
    $view->submissiontext = $submissiontext;
    $view->submissiondate = $submissiondate;
    $view->cm = $voiceshadow->coursemodule;

    $views[] = $view;
}

//===================
// DISPLAY
//===================

$PAGE->set_title($str->voiceshadows);
echo $OUTPUT->header();

foreach ($views as $view) {
    echo $OUTPUT->container_start('clearfix generalbox voiceshadow');

    // info bit
    echo $OUTPUT->heading("$view->section - $view->name", 3, 'mdl-left');
    if (!empty($view->submitted)) {
        echo '<div class="reportlink">'.$view->submitted.'</div>';
    }

    // description part
    echo '<div class="description">'.$view->description.'</div>';

    //submission part
    echo $OUTPUT->container_start('generalbox submission');
    echo '<div class="submissiondate">'.$view->submissiondate.'</div>';
    echo "<p class='no-overflow'>$view->submissiontext</p>\n";
    echo "<p>$view->editlink</p>\n";
    echo $OUTPUT->container_end();

    // feedback part
    $onlineinstance = new voiceshadow_online( $view->cm );
    $onlineinstance->view_feedback();

    echo $OUTPUT->container_end();
}

echo $OUTPUT->footer();