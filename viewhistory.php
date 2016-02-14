<?php  // $Id: viewhistory.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $


require_once '../../config.php';
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once 'lib.php';
require_once ($CFG->libdir.'/gradelib.php');


$id                     = optional_param('id', 0, PARAM_INT); 
$ids                    = optional_param('ids', 0, PARAM_INT); 
$a                      = optional_param('a', 'list', PARAM_TEXT);  
    

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

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (voiceshadow_is_ios() && is_dir($CFG->dirroot.'/theme/mymobile')) {} else
  $PAGE->requires->js('/mod/voiceshadow/js/jquery.min.js', true);
  
$PAGE->requires->js('/mod/voiceshadow/js/flowplayer.min.js', true);
$PAGE->requires->js('/mod/voiceshadow/js/swfobject.js', true);


/// Print the page header
$strvoiceshadows = get_string('modulenameplural', 'voiceshadow');
$strvoiceshadow  = get_string('modulename', 'voiceshadow');

$PAGE->set_url('/mod/voiceshadow/viewhistory.php', array('id' => $id, 'ids' => $ids));
    
$title = $course->shortname . ': ' . format_string(get_string('modulename', 'voiceshadow'));
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

require_once ('tabs.php');

/// Print the main part of the page

    $table = new html_table();

    $table->head  = array(get_string("cell1::student", "voiceshadow"), get_string("cell2::", "voiceshadow"), get_string("cell3::peer", "voiceshadow"), get_string("cell4::teacher", "voiceshadow"));
    $table->align = array ("left", "center", "center", "center");
    $table->width = "100%";
        
    $lists = $DB->get_records ("voiceshadow_files", array("userid" => $ids), 'time DESC');

    foreach ($lists as $list) {
      if ($cml = get_coursemodule_from_id('voiceshadow', $list->instance)) {
        if ($cml->course == $cm->course) {
          $name = "var".$list->var."text";
          
          $userdata    = $DB->get_record("user", array("id" => $list->userid));
          $picture     = $OUTPUT->user_picture($userdata, array('popup' => true));
                  
          $own = $DB->get_record("voiceshadow_ratings", array("fileid" => $list->id, "userid" => $list->userid));
              
          if (@empty($own->ratingrhythm)) @$own->ratingrhythm = get_string('norateyet', 'voiceshadow');
          if (empty($own->ratingclear))  $own->ratingclear = get_string('norateyet', 'voiceshadow');
          if (empty($own->ratingintonation)) $own->ratingintonation = get_string('norateyet', 'voiceshadow');
          if (empty($own->ratingspeed)) $own->ratingspeed = get_string('norateyet', 'voiceshadow');
          if (empty($own->ratingreproduction)) $own->ratingreproduction = get_string('norateyet', 'voiceshadow');
              
          //1-cell
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
          
          if ($list->userid == $USER->id || has_capability('mod/voiceshadow:teacher', $context)) {
            if ($list->userid == $USER->id)
              $editlink   = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $list->instance, "a" => "add", "fileid" => $list->id)), get_string("editlink", "voiceshadow"))." ";
            else
              $editlink   = "";
            
            
            if (has_capability('mod/voiceshadow:teacher', $context) || ($voiceshadow->resubmit == 1 && $list->userid == $USER->id)) { //ONLY TEACHER CAN DELETE SUBMISSION
              $deletelink = html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $list->instance, "act" => "deleteentry", "fileid" => $list->id)), get_string("delete", "voiceshadow"), array("onclick"=>"return confirm('".get_string("confim", "voiceshadow")."')"));
              $o .= html_writer::tag('div', html_writer::tag('small', $editlink.$deletelink, array("style" => "margin: 2px 0 0 10px;")));
            }
          }
          
          $cell1 = new html_table_cell($o);
          
          //2-cell
          $table2 = new html_table();

          $table2->head  = array(get_string("table2::cell1::pronunciation", "voiceshadow"), get_string("table2::cell2::fluency", "voiceshadow"), get_string("table2::cell3::content", "voiceshadow"), get_string("table2::cell4::organization", "voiceshadow"), get_string("table2::cell5::eye", "voiceshadow"));
          //$table2->align = array ("center", "center", "center", "center", "center");
          $table2->align = array ("center".get_string("table2::style", "voiceshadow"), "center".get_string("table2::style", "voiceshadow"), "center".get_string("table2::style", "voiceshadow"), "center".get_string("table2::style", "voiceshadow"), "center".get_string("table2::style", "voiceshadow"));
          $table2->width = "100%";
          
          $table2->data[] = array (voiceshadow_set_rait($list->id, 1),
                                   voiceshadow_set_rait($list->id, 2),
                                   voiceshadow_set_rait($list->id, 3),
                                   voiceshadow_set_rait($list->id, 4),
                                   voiceshadow_set_rait($list->id, 5));
          
          //----Comment Box-----/
          //if ($list->userid == $USER->id){
          $chtml = "";
          if($comments = $DB->get_records("voiceshadow_comments", array("fileid" => $list->id))){
            foreach($comments as $comment){
              $chtml .= html_writer::start_tag('div', array("style"=>"border:1px solid #333;margin:5px;text-align:left;padding:5px;"));
              
              $chtml .= html_writer::tag('div', $comment->summary, array('style'=>'margin:10px 0;'));
              
              if (!empty($comment->itemid))
                $chtml .= html_writer::tag('div', voiceshadow_player($comment->id, "voiceshadow_comments"));
                
              $chtml .= html_writer::tag('div', html_writer::tag('small', date(get_string("timeformat1", "voiceshadow"), $comment->time)), array("style" => "float:left;"));
              
              if ($comment->userid == $USER->id || has_capability('mod/voiceshadow:teacher', $context)) {
                $student = $DB->get_record("user", array("id" => $comment->userid));
                $studentlink = html_writer::link(new moodle_url('/user/view.php', array("id" => $student->id, "course" => $cml->course)), fullname($student));
                $chtml .= html_writer::tag('div', html_writer::tag('small', $studentlink . " " . html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $list->instance, "act" => "deletecomment", "fileid" => $comment->id)), get_string("delete", "voiceshadow"), array("onclick"=>"return confirm('".get_string("confim", "voiceshadow")."')")), array("style" => "margin: 2px 0 0 10px;")));
              }
              
              $chtml .= html_writer::end_tag('div');
            }
          }
          
          $addcommentlink = html_writer::tag('div', html_writer::link(new moodle_url('/mod/voiceshadow/view.php', array("id" => $list->instance, "a" => "add", "act" => "addcomment", "fileid" => $list->id)), get_string("addcomment", "voiceshadow")));
            /*
          } else
            $addcomment = "";*/
          //--------------------/
          
          $cell2 = new html_table_cell(html_writer::table($table2) . $chtml . $addcommentlink);
          
          //3-cell
          $cell3 = new html_table_cell(voiceshadow_set_rait($list->id, 6));
          
          //4-cell
          $cell4 = new html_table_cell(voiceshadow_set_rait($list->id, 7));
          
          
          $cells = array($cell1, $cell2, $cell3, $cell4);
          
          $row = new html_table_row($cells);
              
          $table->data[] = $row;
        }
      }
    }
   
    echo html_writer::table($table);

    if (isset($list))
      echo html_writer::script('
 $(document).ready(function() {
  $(".voiceshadow_rate_box").change(function() {
    var value = $(this).val();
    var data  = $(this).attr("data-url");
    
    var e = $(this).parent();
    e.html(\'<img src="img/ajax-loader.gif" />\');
    
    $.get("ajax.php", {id: '.$list->instance.', act: "setrating", data: data, value: value}, function(data) {
      e.html(data); 
    });
  });
 });
    ');

/// Finish the page
echo $OUTPUT->footer();



