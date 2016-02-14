<?php

/**
 * Define the complete voiceshadow structure for backup, with file and id annotations
 */     
class backup_voiceshadow_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $voiceshadow = new backup_nested_element('voiceshadow', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timeopen', 'timeclose', 'teacher', 'embedvideo', 'recordtype', 'timemodified'));
        
        $files = new backup_nested_element('files', array('id'), array(
            'instance', 'userid', 'summary', 'itemoldid', 'itemid', 'itemimgid', 'filename', 'time'));
        
        $ratings = new backup_nested_element('ratings', array('id'), array(
            'fileid', 'userid', 'rating', 'ratingrhythm', 'ratingclear', 'ratingintonation', 'ratingspeed', 'ratingreproduction', 'summary', 'time'));
        
        $comments = new backup_nested_element('comments', array('id'), array(
            'instance', 'fileid', 'userid', 'summary', 'itemoldid', 'itemid', 'itemimgid', 'filename', 'time'));
        
        $process = new backup_nested_element('process', array('id'), array(
            'itemid', 'type'));
        
        // Build the tree
        $voiceshadow->add_child($files);
        $voiceshadow->add_child($ratings);
        $voiceshadow->add_child($comments);
        $voiceshadow->add_child($process);
        
        // Define sources
        $voiceshadow->set_source_table('voiceshadow', array('id' => backup::VAR_ACTIVITYID, 'course' => backup::VAR_COURSEID));
        $files->set_source_table('voiceshadow_files', array('instance' => backup::VAR_ACTIVITYID));
        $comments->set_source_table('voiceshadow_comments', array('instance' => backup::VAR_ACTIVITYID));
 
        // Define id annotations
        $voiceshadow->annotate_ids('teacher', 'userid');
        $files->annotate_ids('userid', 'userid');
        $ratings->annotate_ids('userid', 'userid');
        $comments->annotate_ids('userid', 'userid');
 
        // Return the root element (voiceshadow), wrapped into standard activity structure
        
        return $this->prepare_activity_structure($voiceshadow);
    }
}