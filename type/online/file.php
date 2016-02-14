<?php

require("../../../../config.php");
require("../../lib.php");
require("voiceshadow.class.php");

$id     = required_param('id', PARAM_INT);      // Course Module ID
$userid = required_param('userid', PARAM_INT);  // User ID

$PAGE->set_url('/mod/voiceshadow/type/online/file.php', array('id'=>$id, 'userid'=>$userid));

if (! $cm = get_coursemodule_from_id('voiceshadow', $id)) {
    print_error('invalidcoursemodule');
}

if (! $voiceshadow = $DB->get_record("voiceshadow", array("id"=>$cm->instance))) {
    print_error('invalidid', 'voiceshadow');
}

if (! $course = $DB->get_record("course", array("id"=>$voiceshadow->course))) {
    print_error('coursemisconf', 'voiceshadow');
}

if (! $user = $DB->get_record("user", array("id"=>$userid))) {
    print_error('usermisconf', 'voiceshadow');
}

require_login($course->id, false, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (($USER->id != $user->id) && !has_capability('mod/voiceshadow:grade', $context)) {
    print_error('cannotviewvoiceshadow', 'voiceshadow');
}

if ($voiceshadow->voiceshadowtype != 'online') {
    print_error('invalidtype', 'voiceshadow');
}

$voiceshadowinstance = new voiceshadow_online($cm->id, $voiceshadow, $cm, $course);


$PAGE->set_pagelayout('popup');
$PAGE->set_title(fullname($user,true).': '.$voiceshadow->name);

$PAGE->requires->js('/mod/voiceshadow/js/jquery.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/flowplayer.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/swfobject.js', true);

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox boxaligcenter', 'dates');

$lists = $DB->get_records ("voiceshadow_files", array("userid" => $user->id), 'time DESC');


$table        = new html_table();
$table->head  = array(get_string("voiceshadow_list", "voiceshadow"));
$table->align = array ("left");
$table->width = "100%";

foreach ($lists as $list) {
  if ($cml = get_coursemodule_from_id('voiceshadow', $list->instance)) {
    if ($cml->course == $cm->course && $cml->instance == $cm->instance) {
      $name = "var".$list->var."text";
      
      $userdata  = $DB->get_record("user", array("id" => $list->userid));
      $picture   = $OUTPUT->user_picture($userdata, array('popup' => true));
      
      $o = "";
      $o .= html_writer::start_tag('div', array("style" => "text-align:left;margin:10px 0;"));
      $o .= html_writer::tag('span', $picture);
      $o .= html_writer::start_tag('span', array("style" => "margin: 8px;position: absolute;"));
      $o .= html_writer::link(new moodle_url('/user/view.php', array("id" => $userdata->id, "course" => $cml->course)), fullname($userdata));
      $o .= html_writer::end_tag('span');
      $o .= html_writer::end_tag('div');
      
      $o .= html_writer::tag('div', $list->summary, array('style'=>'margin:10px 0;'));
      
      $o .= html_writer::tag('div', voiceshadow_player($list->id));
      
      if (!empty($voiceshadow->{$name}))
        $o .= html_writer::tag('div', "(".$voiceshadow->{$name}.")");
      
      $o .= html_writer::tag('div', html_writer::tag('small', date(get_string("timeformat1", "voiceshadow"), $list->time)), array("style" => "float:left;"));
      
      $cell1 = new html_table_cell($o);
      
      $cells = array($cell1);
      
      $row = new html_table_row($cells);
      
      $table->data[] = $row;
    }
  }
}

echo html_writer::table($table);

echo $OUTPUT->box_end();
echo $OUTPUT->close_window_button();
echo $OUTPUT->footer();

