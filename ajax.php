<?php

require_once '../../config.php';
require_once 'lib.php';


$data                      = optional_param('data', 0, PARAM_TEXT); 
$value                     = optional_param('value', 0, PARAM_INT); 

list($fileid, $type) = explode("::", $data);

if (!empty($data) && !empty($value)) {
  if($type == 6 || $type == 7) {
    $typesql = 'rating';
  } else if ($type == 5) {
    $typesql = 'ratingreproduction';
  } else if ($type == 4) {
    $typesql = 'ratingspeed';
  } else if ($type == 3) {
    $typesql = 'ratingintonation';
  } else if ($type == 2) {
    $typesql = 'ratingclear';
  } else if ($type == 1) {
    $typesql = 'ratingrhythm';
  }
  
  if (!$voiceshadowid = $DB->get_record("voiceshadow_ratings", array("fileid" => $fileid, "userid" => $USER->id))) {
    $add                = new stdClass;
    $add->fileid        = $fileid;
    $add->userid        = $USER->id;
    $add->$typesql      = $value;
    $add->time          = time();
    
    $DB->insert_record("voiceshadow_ratings", $add);
  } else {
    $DB->set_field("voiceshadow_ratings", $typesql, $value, array("fileid" => $fileid, "userid" => $USER->id));
  }
  
  echo $value;
  
  if ($typesql == 'rating'){
      $voiceshadowid = $DB->get_record("voiceshadow_ratings", array("fileid" => $fileid, "userid" => $USER->id));
      $voiceshadowfiles = $DB->get_record("voiceshadow_files", array("id" => $voiceshadowid->fileid));
      $cm = get_coursemodule_from_id('voiceshadow', $voiceshadowfiles->instance);
      $context = get_context_instance(CONTEXT_MODULE, $cm->id);
      
      $voiceshadow = $DB->get_record("voiceshadow", array("id"=>$cm->instance));
      
      //-----Set grade----//
      
      if (has_capability('mod/voiceshadow:teacher', $context)) {
          $catdata  = $DB->get_record("grade_items", array("courseid" => $cm->course, "iteminstance"=> $voiceshadow->id, "itemmodule" => 'voiceshadow'));
          $gradesdata               = new object;
          $gradesdata->itemid       = $catdata->id;
          $gradesdata->userid       = $voiceshadowfiles->userid;
          $gradesdata->rawgrade     = $value;
          $gradesdata->finalgrade   = $value;
          $gradesdata->rawgrademax  = $catdata->grademax;
          $gradesdata->usermodified = $voiceshadowfiles->userid;
          $gradesdata->timecreated  = time();
          $gradesdata->time         = time();
                
          if (!$grid = $DB->get_record("grade_grades", array("itemid" => $gradesdata->itemid, "userid" => $gradesdata->userid))) {
              $grid = $DB->insert_record("grade_grades", $gradesdata);
          } else {
              $gradesdata->id = $grid->id;
              $DB->update_record("grade_grades", $gradesdata);
          }
          
          //Count all grades
          
          $filesincourse = $DB->get_records("voiceshadow_files", array("instance" => $voiceshadowfiles->instance, "userid" => $voiceshadowfiles->userid), 'id', 'id');
          
          $filessql = '';
          
          foreach($filesincourse as $filesincourse_){
            $filessql .= $filesincourse_->id.",";
          }
          
          $filessql = substr($filessql, 0, -1);
          
          $allvoites = $DB->get_records_sql("SELECT `id`, `rating`, `userid` FROM {voiceshadow_ratings} WHERE `fileid` IN ({$filessql})");
          
          $rate = 0;
          $c = 0;
          foreach ($allvoites as $allvoite) {
              if (has_capability('mod/voiceshadow:teacher', $context, $allvoite->userid) && !empty($allvoite->rating)) {
                $rate += $allvoite->rating;
                $c++;
              }
          }

          if ($c > 0)
            $rate = round ($rate/$c,1);
          
          $gradesdata->rawgrade   = $rate;
          $gradesdata->finalgrade = $rate;
          
          if(empty($gradesdata->id)) 
            $gradesdata->id = $grid;
          
          $DB->update_record("grade_grades", $gradesdata);
      }
      
      //------------------//
  }
  
  die();
  
}

    if (!$voiceshadowid = $DB->get_record("voiceshadow_ratings", array("fileid" => $fileid, "userid" => $USER->id))) {
        
        $data                = new stdClass;
        $data->fileid        = $fileid;
        $data->userid        = $USER->id;
        if (!empty($rating)) $data->rating        = $rating;
        if (!empty($ratingRhythm)) $data->ratingrhythm = $ratingRhythm;
        if (!empty($ratingclear)) $data->ratingclear = $ratingclear;
        if (!empty($ratingintonation)) $data->ratingintonation = $ratingintonation;
        if (!empty($ratingspeed)) $data->ratingspeed = $ratingspeed;
        if (!empty($ratingreproduction)) $data->ratingreproduction = $ratingreproduction;
        $data->time  = time();
            
        $DB->insert_record("voiceshadow_ratings", $data);
            
        $allvoites = $DB->get_records("voiceshadow_ratings", array("fileid" => $fileid));
            
        $rate = 0;
        $c    = 0;

        foreach ($allvoites as $allvoite) {
          if ($allvoite->rating > 0) {
            $rate += $allvoite->rating;
            $c++;
          }
        }
        $rate = round ($rate/$c,1);
            
            
        if (!empty($ratingRhythm)) $rate = $ratingRhythm;
        if (!empty($ratingclear)) $rate = $ratingclear;
        if (!empty($ratingintonation)) $rate = $ratingintonation;
        if (!empty($ratingspeed)) $rate = $ratingspeed;
        if (!empty($ratingreproduction)) $rate = $ratingreproduction;
            
        echo $rate;
        die();
    } else { 
        if (!empty($rating)) $DB->set_field("voiceshadow_ratings", "rating", $rating, array("id" => $voiceshadowid->id));
        if (!empty($ratingRhythm)) $DB->set_field("voiceshadow_ratings", "ratingrhythm", $ratingRhythm, array("id" => $voiceshadowid->id));
        if (!empty($ratingclear)) $DB->set_field("voiceshadow_ratings", "ratingclear", $ratingclear, array("id" => $voiceshadowid->id));
        if (!empty($ratingintonation)) $DB->set_field("voiceshadow_ratings", "ratingintonation", $ratingintonation, array("id" => $voiceshadowid->id));
        if (!empty($ratingspeed)) $DB->set_field("voiceshadow_ratings", "ratingspeed", $ratingspeed, array("id" => $voiceshadowid->id));
        if (!empty($ratingreproduction)) $DB->set_field("voiceshadow_ratings", "ratingreproduction", $ratingreproduction, array("id" => $voiceshadowid->id));
            
            
        $allvoites = $DB->get_records("voiceshadow_ratings", array("fileid" => $fileid));
            
        $rate = 0;
        $c = 0;

        foreach ($allvoites as $allvoite) {
          if ($allvoite->rating > 0) {
            $rate += $allvoite->rating;
            $c++;
          }
        }
        
        $rate = round ($rate/$c,1);
            

        if (!empty($ratingRhythm)) $rate = $ratingRhythm;
        if (!empty($ratingclear)) $rate = $ratingclear;
        if (!empty($ratingintonation)) $rate = $ratingintonation;
        if (!empty($ratingspeed)) $rate = $ratingspeed;
        if (!empty($ratingreproduction)) $rate = $ratingreproduction;
            
        echo $rate;
        die();
    }