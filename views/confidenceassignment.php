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

    $submissions_data = $DB->get_records_sql('SELECT * FROM {workshop_submissions} AS mws INNER JOIN {workshop_assessments} AS mwa ON mwa.submissionid=mws.id WHERE mwa.reviewerid!=mws.authorid', array());

    foreach ($submissions_data as $submission_data) {

        // ID della risoluzione
        $submissionid = $submission_data->submissionid;
        // Autore dell'assessment per la risoluzione
        $evaluatorid = $submission_data->reviewerid;

        $temp = $DB->get_records_sql('SELECT * FROM {mpa_confidence_levels} WHERE id=? AND evaluatorid=?', array($submissionid, $evaluatorid));

        if (empty($temp)) {
            $DB->execute('INSERT INTO {mpa_confidence_levels} (id,evaluatorid,confidence_level) VALUES (?,?,?)', $parms = array($submissionid, $evaluatorid, 0));
        }

    }

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