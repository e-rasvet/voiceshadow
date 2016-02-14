<?php  // $Id: uploadmp3.php,v 1.2 2012/03/10 22:00:00 Igor Nikulin Exp $


    require_once '../../config.php';
    require_once 'lib.php';
    
    $filename                     = optional_param('name', NULL, PARAM_TEXT);
    $file                         = optional_param('audio', NULL, PARAM_TEXT);
    $p                            = optional_param('p', NULL, PARAM_TEXT);
    
    $p = json_decode(urldecode($p));

    $id                           = $p->id;
    $userid                       = $p->userid;
    
    if (strstr($file, "data:audio/mp3;base64,")) 
      $file = str_replace("data:audio/mp3;base64,", "", $file);
    
    
    $file = base64_decode($file);
    
    $student = $DB->get_record("user", array("id" => $userid));
    
    if (!empty($id))
      $context = get_context_instance(CONTEXT_MODULE, $id);
    else
      $context = get_context_instance(CONTEXT_USER, $userid);
    
    $fs = get_file_storage();
    
///Delete old records
    //$fs->delete_area_files($context->id, 'mod_voiceshadow', 'private', $fid);
      
    $file_record = new stdClass;
    
    if (!empty($id)) {
      $file_record->component = 'mod_voiceshadow';
      $file_record->filearea  = 'private';
    } else {
      $file_record->component = 'user';
      $file_record->filearea  = 'public';
    }
    
    
    //if(isset($p->itemid) && is_numeric($p->itemid))
    //  $fid                    = $p->itemid;
    //else {
    //  $fid                    = (int)substr(time(), 2).rand(0,9) + 0;
    /*
      if (!empty($id)) {
        if (!$data = $DB->get_record_sql("SELECT itemid FROM {files} WHERE component='mod_voiceshadow' AND filearea='private' ORDER BY itemid DESC LIMIT 1", array($context->id))) { //AND contextid=?
            $fid = 1;
        } else {
            $fid = $data->itemid + 1;
        }
      } else {
        if (!$data = $DB->get_record_sql("SELECT itemid FROM {files} WHERE component='user' AND filearea='public' ORDER BY itemid DESC LIMIT 1", array($context->id))) {
            $fid = 1;
        } else {
            $fid = $data->itemid + 1;
        }
      }
      */
    //}
    
    if(isset($p->itemid) && is_numeric($p->itemid)) {
      $s = 0;
      $fid                    = $p->itemid;
    } else {
      $s = 1;
      
      $fid                    = (int)substr(time(), 2).rand(0,9) + 0;
      if ($files = $fs->get_area_files($context->id, 'mod_voiceshadow', 'private', $fid)){
        $s = 2;
        $fid = rand(1, 999999999);
        while ($files = $fs->get_area_files($context->id, 'mod_voiceshadow', 'private', $fid)) {
          $s = 3;
          $fid = rand(1, 999999999);
        }
      }
    }
    
    
    $file_record->contextid = $context->id;
    $file_record->userid    = $userid;
    $file_record->filepath  = "/";
    $file_record->itemid    = $fid;
    $file_record->license   = $CFG->sitedefaultlicense;
    $file_record->author    = fullname($student);
    $file_record->source    = '';
    $file_record->filename  = $filename.".mp3";
    
    $to = $CFG->dataroot."/temp/".$filename.".mp3";

    file_put_contents($to, $file);
    
    $itemid = $fs->create_file_from_pathname($file_record, $to);
    
    $json = array("id" => $itemid->get_id());
    
    $item = $DB->get_record("files", array("id"=>$itemid->get_id()));
    
    echo json_encode(array("id"=>$fid, "url"=>"/pluginfile.php/".$item->contextid."/mod_voiceshadow/".$id."/".$item->id."/".$item->filename));
    //, "text"=>voiceshadow_runExternal("python /var/www/html/moodle/_py/speechtotextmp3.py {$to}")
    //echo json_encode(array("id"=>$itemid->get_id(), "url"=>(new moodle_url("/mod/voiceshadow/js/recorder.swf"))));

    unlink($to);

    