<?php

/**
 * Pagina di visualizzazione dei punteggi risolutore e valutatore per tutti gli studenti.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/student.php');

if (isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);
    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

    $renderer = $PAGE->get_renderer('local_mpa');


    if (has_capability('local/mpa:studentscore', $usercontext, $userid)) {

        $submissions_data = $DB->get_records_sql('SELECT *
        FROM {workshop_submissions} AS mws INNER JOIN {workshop_assessments} AS mwa ON mwa.submissionid=mws.id WHERE mwa.reviewerid!=mws.authorid', array());

        foreach ($submissions_data as $submission_data){

            // ID della risoluzione
            $submissionid = $submission_data->submissionid;
            // Autore dell'assessment per la risoluzione
            $evaluatorid = $submission_data->reviewerid;
            // Autore della risoluzioned
            $solverid = $submission_data->authorid;

            $temp = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid,$evaluatorid,$solverid));


            if (empty($temp)) {
                $DB->execute('INSERT INTO {mpa_submission_data} (id,evaluatorid,solverid,submission_steadiness,submission_score,assessment_value,assessment_goodness)
                    VALUES (?,?,?,?,?,?,?)',$parms=array($submissionid,$evaluatorid,$solverid,null,null,null,null));
            } else {
                $DB->execute('UPDATE {mpa_submission_data}
                    SET id=?, evaluatorid=?, solverid=?, submission_steadiness=?,submission_score=?, assessment_value=?,assessment_goodness=? WHERE id=? AND evaluatorid=? AND solverid=?',
                    $parms = array($submissionid,$evaluatorid,$solverid,null,null,null,null,$submissionid,$evaluatorid,$solverid));
            }
        }

        echo $renderer->render_student_scores();
    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}