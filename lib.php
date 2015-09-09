<?php
/**
 * Interazione con il core di Moodle.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

function local_mpa_extends_navigation(global_navigation $navigation)
{

    global $CFG;

    $baseMenu = $navigation->add(get_string('pluginname', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/index.php'), null, null, null);

    $baseMenu->add(get_string('mpa:studentsummary', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/views/studentsummary.php'), null, null, null);
    $baseMenu->add(get_string('mpa:studentfeedback', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/views/studentfeedback.php'), null, null, null);
    $baseMenu->add(get_string('mpa:confidenceassignment', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/views/confidenceassignment.php'), null, null, null);
    $baseMenu->add(get_string('mpa:studentscore', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/views/studentscore.php'), null, null, null);
    $baseMenu->add(get_string('mpa:exportdata', 'local_mpa'), new moodle_url($CFG->wwwroot . '/local/mpa/views/exportdata.php'), null, null, null);

    $baseMenu->forceopen = true;
}


