<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/voiceshadow/lib.php');

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('voiceshadow_maxbytes', get_string('maximumsize', 'voiceshadow'),
                           get_string('configmaxbytes', 'voiceshadow'), 1048576, get_max_upload_sizes($CFG->maxbytes)));
    }

    $options = array(VOICESHADOW_COUNT_WORDS   => trim(get_string('numwords', '', '?')),
                     VOICESHADOW_COUNT_LETTERS => trim(get_string('numletters', '', '?')));
    $settings->add(new admin_setting_configselect('voiceshadow_itemstocount', get_string('itemstocount', 'voiceshadow'),
                       get_string('configitemstocount', 'voiceshadow'), VOICESHADOW_COUNT_WORDS, $options));

    $settings->add(new admin_setting_configcheckbox('voiceshadow_showrecentsubmissions', get_string('showrecentsubmissions', 'voiceshadow'),
                       get_string('configshowrecentsubmissions', 'voiceshadow'), 1));
                       
    // Converting method
    $options = array();
    $options[1] = get_string('usemediaconvert', 'voiceshadow');
    $options[2] = get_string('usethisserver', 'voiceshadow');
    $settings->add(new admin_setting_configselect('voiceshadow_convert',
            get_string('convertmethod', 'voiceshadow'), get_string('descrforconverting', 'voiceshadow'), 1, $options));
            
    //preplayer
    
    $options = array();
    $options[1] = get_string('yes');
    $options[2] = get_string('no');
    $settings->add(new admin_setting_configselect('voiceshadow_preplayer',
            get_string('preplayer', 'voiceshadow'), get_string('preplayerdescr', 'voiceshadow'), 1, $options));
            
    // Converting url
    $settings->add(new admin_setting_configtext('voiceshadow_convert_url',
            get_string('converturl', 'voiceshadow'), get_string('descrforconvertingurl', 'voiceshadow'), '', PARAM_URL));
}
