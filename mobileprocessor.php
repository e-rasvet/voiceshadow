<?php

    require_once("../../config.php");
    require_once("lib.php");
    require_once("classAudioFile.php");
    
    $id                     = optional_param('id', 0, PARAM_INT);
    $uid                    = optional_param('uid', 0, PARAM_INT);
    $time                   = optional_param('time', 0, PARAM_INT);
    $var                    = optional_param('gvar', 0, PARAM_INT);
    
    if ($id) {
        if (! $cm = $DB->get_record("course_modules", array("id"=> $id))) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = $DB->get_record("course", array("id"=> $cm->course))) {
            error("Course is misconfigured");
        }
    
        if (! $voiceshadow = $DB->get_record("voiceshadow", array("id"=> $cm->instance))) {
            error("Course module is incorrect");
        }
    } else {
        if (! $voiceshadow = $DB->get_record("voiceshadow", array("id"=> $a))) {
            error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id"=> $voiceshadow->course))) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("voiceshadow", $voiceshadow->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
    
    $name = "var{$var}";
    
    $linktofile = $CFG->wwwroot.'/mod/voiceshadow/file.php?file='.$voiceshadow->{$name};
    $file       = voiceshadow_getfileid($voiceshadow->{$name});
    
    $AF = new AudioFile;
    if (is_file($file->fullpatch)) {
      $AF->loadFile($file->fullpatch);
      $duration = round($AF->wave_length);
      
      if (empty($duration)) {
        $m = new mp3file($file->fullpatch);
        $a = $m->get_metadata();
        $duration = $a['Length'];
      }
    }
    
    if ($uid)
      $USER = $DB->get_record("user", array("id"=> $uid));
    
    if (empty($time))
      $time = time();
    
    $json = array(
      "play"     => $linktofile,
      "title"    => $voiceshadow->name,
      "descr"    => strip_tags($voiceshadow->intro),
      "type"     => 'voiceshadow',
      "id"       => $id,
      "cid"      => $course->id,
      "uid"      => $USER->id,
      "filename" => str_replace(" ", "_", $USER->username)."_".date("Ymd_Hi", $time),
      "duration" => $duration
    );
    
    echo json_encode($json);
    
