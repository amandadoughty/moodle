<?php

defined('MOODLE_INTERNAL') || die;
global $DB;
//require_once($CFG->libdir . '/filelib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_culschool_html_allowcssclasses', get_string('allowadditionalcssclasses', 'block_culschool_html'),
                       get_string('configallowadditionalcssclasses', 'block_culschool_html'), 0));

    

    $categories = $DB->get_records('course_categories', array ('visible' => 1), 'id, name');

    foreach ($categories as $category) {

        $catid = $category->id;
        $catname = $category->name;

        $settings->add(new admin_setting_confightmleditor('block_culschool_html/student'.$catid, new lang_string('student'.$catid, 'block_culschool_html'),
        new lang_string('studentdesc'.$catid, 'block_culschool_html'), '', PARAM_RAW));

        $settings->add(new admin_setting_confightmleditor('block_culschool_html/staff'.$catid, new lang_string('staff'.$catid, 'block_culschool_html'),
        new lang_string('staffdesc'.$catid, 'block_culschool_html'), '', PARAM_RAW));

    }

    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/2student', new lang_string('LLILAWstudent', 'block_culschool_html'),
    //     new lang_string('LLILAWstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/6student', new lang_string('GELLBstudent', 'block_culschool_html'),
    //     new lang_string('GELLBstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/7student', new lang_string('LLBCOstudent', 'block_culschool_html'),
    //     new lang_string('LLBCOstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/3student', new lang_string('BBCASSstudent', 'block_culschool_html'),
    //     new lang_string('BBCASSstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/BBMBABstudent', new lang_string('BBMBABstudent', 'block_culschool_html'),
    //     new lang_string('BBMBABstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/5student', new lang_string('BBMSCBstudent', 'block_culschool_html'),
    //     new lang_string('BBMSCBstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/AASOARstudent', new lang_string('AASOARstudent', 'block_culschool_html'),
    //     new lang_string('AASOARstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/1student', new lang_string('HSSOHSstudent', 'block_culschool_html'),
    //     new lang_string('HSSOHSstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/EESEMSstudent', new lang_string('EESEMSstudent', 'block_culschool_html'),
    //     new lang_string('EESEMSstudentdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/otherstudent', new lang_string('otherstudent', 'block_culschool_html'),
    //     new lang_string('otherstudentdesc', 'block_culschool_html'), '', PARAM_RAW));

    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/2staff', new lang_string('LLILAWstaff', 'block_culschool_html'),
    //     new lang_string('LLILAWstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/6staff', new lang_string('GELLBstaff', 'block_culschool_html'),
    //     new lang_string('GELLBstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/7staff', new lang_string('LLBCOstaff', 'block_culschool_html'),
    //     new lang_string('LLBCOstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/3staff', new lang_string('BBCASSstaff', 'block_culschool_html'),
    //     new lang_string('BBCASSstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/BBMBABstaff', new lang_string('BBMBABstaff', 'block_culschool_html'),
    //     new lang_string('BBMBABstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/5staff', new lang_string('BBMSCBstaff', 'block_culschool_html'),
    //     new lang_string('BBMSCBstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/AASOARstaff', new lang_string('AASOARstaff', 'block_culschool_html'),
    //     new lang_string('AASOARstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/1staff', new lang_string('HSSOHSstaff', 'block_culschool_html'),
    //     new lang_string('HSSOHSstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/EESEMSstaff', new lang_string('EESEMSstaff', 'block_culschool_html'),
    //     new lang_string('EESEMSstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
    // $settings->add(new admin_setting_confightmleditor('block_culschool_html/otherstaff', new lang_string('otherstaff', 'block_culschool_html'),
    //     new lang_string('otherstaffdesc', 'block_culschool_html'), '', PARAM_RAW));
}



