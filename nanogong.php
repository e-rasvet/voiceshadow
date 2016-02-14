<?php  // $Id: nanogong.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $


    require_once '../../config.php';
    require_once 'lib.php';
    
    

    $id                           = optional_param('id', 0, PARAM_INT);
    $userid                       = optional_param('userid', 0, PARAM_INT);
    $fid                          = optional_param('fid', 0, PARAM_INT);
    $filename                     = optional_param('filename', NULL, PARAM_TEXT);
    
    
    $student = $DB->get_record("user", array("id" => $userid));
    
    if (empty($id)) 
      $context = get_context_instance(CONTEXT_USER, $userid);
    else
      $context = get_context_instance(CONTEXT_MODULE, $id);
    
    
    $fs = get_file_storage();
      
    $file_record = new stdClass;
    $file_record->component = 'mod_voiceshadow';
    $file_record->contextid = $context->id;
    $file_record->userid    = $userid;
    $file_record->filearea  = 'private';
    $file_record->filepath  = "/";
    $file_record->itemid    = $fid;
    $file_record->license   = $CFG->sitedefaultlicense;
    $file_record->author    = fullname($student);
    $file_record->source    = '';
    $file_record->filename  = $filename.".wav";
    
    if ($_FILES['voicefile']['tmp_name'] && $_FILES['voicefile']['size'] > 0) {
        $to = $CFG->dataroot."/temp/".$filename.".wav";
        move_uploaded_file($_FILES['voicefile']['tmp_name'], $to);
        
        $itemid = $fs->create_file_from_pathname($file_record, $to);
        
        $json = array("id" => $itemid->get_id());
        
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        
        echo json_encode($json);

        unlink($to);
    }


