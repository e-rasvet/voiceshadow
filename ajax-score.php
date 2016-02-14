<?php

  require_once '../../config.php';
  require_once 'lib.php';


  $text1                      = optional_param('text1', 0, PARAM_TEXT); 
  $text2                      = optional_param('text2', 0, PARAM_TEXT); 
  
  echo voiceshadow_similar_text($text1, $text2);