<?php // $Id: tabs.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $

    $currenttab = $a;

    if (empty($voiceshadow)) {
        error('You cannot call this script in that way');
    }
    if (empty($currenttab)) {
        $currenttab = 'list';
    }
    if (!isset($cm)) {
        $cm = get_coursemodule_from_instance('voiceshadow', $voiceshadow->id);
    }
    if (!isset($course)) {
        $course = $DB->get_record('course', array('id' => $voiceshadow->course));
    }

    $tabs     = array();
    $row      = array();
    $inactive = array();

    $row[]  = new tabobject('list', new moodle_url('/mod/voiceshadow/view.php', array('id'=>$id)), get_string('voiceshadow_list', 'voiceshadow'));
   
    $showaddbutton = 1;
    
    /*
    if ($voiceshadow->allowmultiple > 0) {
      $data = $DB->count_records("voiceshadow_files", array("instance"=>$id, "userid"=>$USER->id));
      if ($data >= $voiceshadow->allowmultiple)
        $showaddbutton = 0;
    }
    */
    
    if ($voiceshadow->timedue == 0 || ($voiceshadow->timedue > 0 && time() < $voiceshadow->timedue) || $voiceshadow->preventlate == 1)
      if ($showaddbutton == 1)
        $row[]  = new tabobject('add', new moodle_url('/mod/voiceshadow/view.php', array('id'=>$id, 'a'=>'add')), get_string('voiceshadow_add_record', 'voiceshadow'));
    
    
    $row[]  = new tabobject('history', new moodle_url('/mod/voiceshadow/viewhistory.php', array('id'=>$id ,'ids'=>$USER->id, 'a'=>'history')), get_string('voiceshadow_viewhistory', 'voiceshadow'));
    
    $contextmodule = get_context_instance(CONTEXT_MODULE, $cm->id);
    
    if (has_capability('mod/voiceshadow:teacher', $contextmodule))
      $row[]  = new tabobject('historybyuser', new moodle_url('/mod/voiceshadow/viewhistory_by_users.php', array('id'=>$id, 'a'=>'historybyuser')), get_string('voiceshadow_by_student', 'voiceshadow'));
    
    $tabs[] = $row;

    print_tabs($tabs, $currenttab, $inactive);
