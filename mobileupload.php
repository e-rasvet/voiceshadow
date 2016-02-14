<?php //$Id: mobileupload.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $

    require_once("../../config.php");
    require_once("lib.php");
    
    $aid                    = optional_param('id', 0, PARAM_INT);
    $cid                    = optional_param('cid', 0, PARAM_INT);
    $uid                    = optional_param('uid', 0, PARAM_INT);
    $filename               = optional_param('filename', 0, PARAM_TEXT);
    
    $item = $DB->get_record("voiceshadow_files", array("filename" => $filename));
    
    $student = $DB->get_record("user", array("id" => $uid));
    
    $context = get_context_instance(CONTEXT_MODULE, $aid);
    
    $fs = get_file_storage();
        
    $file_record = new stdClass;
    $file_record->component = 'mod_voiceshadow';
    $file_record->contextid = $context->id;
    $file_record->userid    = $item->userid;
    $file_record->filearea  = 'private';
    $file_record->filepath  = "/";
    $file_record->itemid    = $item->id;
    $file_record->license   = $CFG->sitedefaultlicense;
    $file_record->author    = fullname($student);
    $file_record->source    = '';
    
    //move_uploaded_file($_FILES['media']['tmp_name'], '/var/kut/moodledata_netcourse_20/temp/1.m4a');
    
    if ($_FILES['media']['tmp_name']) {
      $file_record->filename  = $filename.".m4a";
      $itemid = $fs->create_file_from_pathname($file_record, $_FILES['media']['tmp_name']);
        
      $DB->set_field("voiceshadow_files", "itemoldid", $itemid->get_id(), array("id" => $item->id));
      
      $add         = new stdClass;
      $add->itemid = $itemid->get_id();
      $add->type   = mimeinfo('type', $file_record->filename);
      $add->status = 'open';
      $add->name   = md5($CFG->wwwroot.'_'.time());
      $add->time   = time();
        
      $DB->insert_record("voiceshadow_process", $add);
    }
    