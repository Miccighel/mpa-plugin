<?php

/**
 * Pagina di assegnazione del livello di confidenza a ciascun assessment effettuato dallo studente.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/student.php');
require_once($CFG->dirroot . '/local/mpa/classes/form.php');

if(isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);

    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

    $renderer = $PAGE->get_renderer('local_mpa');

    $logged_student = new Student($userid);

    $given_assessments = $logged_student->loadGivenAssessments();

    $forms = array();

    for($i=0;$i<sizeof($given_assessments);$i++){
        array_push($forms,new confidence_form());
    }

    echo $renderer->render_confidence_assignment($given_assessments,$logged_student);

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}